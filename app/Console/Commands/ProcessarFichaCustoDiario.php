<?php

namespace App\Console\Commands;

use App\Mail\ProdutosSemFichaMail;
use App\Services\ProdutoCustoEstruturaService;
use App\Services\ProdutoCustoMpService;
use App\Services\ProdutoCustoOpService;
use App\Services\ProdutoEmbalagemService;
use App\Services\ProdutoFichaFechamentoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessarFichaCustoDiario extends Command
{
    protected $signature = 'ficha:custo-diario
                            {--ano= : Ano do fechamento}
                            {--mes= : Mês do fechamento}
                            {--email= : E-mail de destino}
                            {--sem-email : Processa sem enviar e-mail}';

    protected $description = 'Processa a ficha de custo, importa custos de matéria-prima, custos operacionais e envia e-mail com produtos sem ficha.';

    public function handle(
        ProdutoFichaFechamentoService $service,
        ProdutoCustoMpService $produtoCustoMpService,
        ProdutoCustoOpService $produtoCustoOpService,
        ProdutoCustoEstruturaService $produtoCustoEstruturaService,
        ProdutoEmbalagemService $produtoEmbalagemService
    ) {
        $ano = $this->option('ano') ?: date('Y');
        $mes = $this->option('mes') ?: date('m');

        $ano = (int) $ano;
        $mes = (int) $mes;

        $emailDestino = $this->option('email') ?: config('mail.produtos_sem_ficha_email');

        $semEmail = (bool) $this->option('sem-email');

        $emails = collect(explode(',', (string) $emailDestino))
            ->map(function ($email) {
                return trim($email);
            })
            ->filter()
            ->values()
            ->all();

        $resultado = [];
        $resultadoEmbalagem = [];
        $resultadoCustosMp = [];
        $resultadoAplicacaoMp = [];
        $resultadoCustosOp = [];
        $resultadoAplicacaoOp = [];
        $resultadoEstrutura = [];

        try {
            $this->info("Processando ficha de custo {$ano}-" . str_pad($mes, 2, '0', STR_PAD_LEFT) . "...");

            $barraProdutos = null;
            $barraLotes = null;

            $resultado = $service->executarFichaCusto($ano, $mes, function ($evento) use (&$barraProdutos, &$barraLotes) {

                if (($evento['tipo'] ?? '') == 'produtos_inicio') {
                    $this->info("Produtos encontrados: " . $evento['total']);

                    $barraProdutos = $this->output->createProgressBar($evento['total']);
                    $barraProdutos->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
                    $barraProdutos->setMessage('Iniciando produtos...');
                    $barraProdutos->start();

                    return;
                }

                if (($evento['tipo'] ?? '') == 'produto' && $barraProdutos) {
                    $descricao = ($evento['codigo'] ?? '') . ' - ' . ($evento['produto'] ?? '');

                    if (!empty($evento['origem'])) {
                        $descricao .= ' | ' . $evento['origem'];
                    }

                    if (strlen($descricao) > 75) {
                        $descricao = substr($descricao, 0, 72) . '...';
                    }

                    if ($evento['sem_ficha'] ?? false) {
                        $descricao .= ' | sem ficha';
                    } else {
                        $descricao .= ' | itens: ' . ($evento['itens'] ?? 0);
                    }

                    $barraProdutos->setMessage($descricao);
                    $barraProdutos->advance();

                    return;
                }

                if (($evento['tipo'] ?? '') == 'produtos_fim' && $barraProdutos) {
                    $barraProdutos->finish();
                    $this->newLine(2);

                    return;
                }

                if (($evento['tipo'] ?? '') == 'lotes_inicio') {
                    $this->info("Gravando " . $evento['linhas'] . " linhas em " . $evento['total'] . " lote(s)...");

                    $barraLotes = $this->output->createProgressBar($evento['total']);
                    $barraLotes->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
                    $barraLotes->setMessage('Gravando lotes...');
                    $barraLotes->start();

                    return;
                }

                if (($evento['tipo'] ?? '') == 'lote' && $barraLotes) {
                    $barraLotes->setMessage('Lote com ' . ($evento['linhas'] ?? 0) . ' linhas');
                    $barraLotes->advance();

                    return;
                }

                if (($evento['tipo'] ?? '') == 'lotes_fim' && $barraLotes) {
                    $barraLotes->finish();
                    $this->newLine(2);

                    return;
                }
            });

            $this->info('Importando parâmetros de embalagem do ERP...');

            $barraEmbalagem = null;

            $resultadoEmbalagem = $produtoEmbalagemService->importarDoErp(function ($evento) use (&$barraEmbalagem) {
                if (($evento['tipo'] ?? '') == 'embalagem_inicio') {
                    $this->info('Produtos de embalagem encontrados: ' . ($evento['total'] ?? 0));

                    $barraEmbalagem = $this->output->createProgressBar($evento['total']);
                    $barraEmbalagem->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
                    $barraEmbalagem->setMessage('Iniciando embalagem...');
                    $barraEmbalagem->start();

                    return;
                }

                if (($evento['tipo'] ?? '') == 'embalagem_item' && $barraEmbalagem) {
                    $descricao = ($evento['codigo'] ?? '') . ' - ' . ($evento['produto'] ?? '');

                    if (strlen($descricao) > 70) {
                        $descricao = substr($descricao, 0, 67) . '...';
                    }

                    $descricao .= ' | Emb: ' . ($evento['embalagem'] ?? '');
                    $descricao .= ' | %: ' . number_format((float) (($evento['custo_percentual'] ?? 1) * 100), 0, ',', '.') . '%';

                    $barraEmbalagem->setMessage($descricao);
                    $barraEmbalagem->advance();

                    return;
                }

                if (($evento['tipo'] ?? '') == 'embalagem_lote' && $barraEmbalagem) {
                    $barraEmbalagem->setMessage('Gravando lote com ' . ($evento['linhas'] ?? 0) . ' linhas');
                    return;
                }

                if (($evento['tipo'] ?? '') == 'embalagem_fim' && $barraEmbalagem) {
                    $barraEmbalagem->finish();
                    $this->newLine(2);

                    return;
                }
            });

            $this->info('Produtos embalagem lidos: ' . ($resultadoEmbalagem['produtos_lidos'] ?? 0));
            $this->info('Produtos embalagem importados: ' . ($resultadoEmbalagem['produtos_importados'] ?? 0));
            $this->info('Embalagem A: ' . ($resultadoEmbalagem['total_a'] ?? 0));
            $this->info('Embalagem B: ' . ($resultadoEmbalagem['total_b'] ?? 0));
            $this->info('Embalagem C: ' . ($resultadoEmbalagem['total_c'] ?? 0));

            $this->info('Importando custos de matéria-prima do ERP...');

            $resultadoCustosMp = $produtoCustoMpService->importarCustosMpErp($ano, $mes);

            $this->info('Custos MP importados: ' . ($resultadoCustosMp['custos_importados'] ?? 0));
            $this->info('Custos manuais aplicados: ' . ($resultadoCustosMp['custos_manuais_aplicados'] ?? 0));
            $this->info('MPs utilizadas na estrutura: ' . ($resultadoCustosMp['mps_utilizadas_estrutura'] ?? 0));
            $this->info('MPs não utilizadas na estrutura: ' . ($resultadoCustosMp['mps_nao_utilizadas_estrutura'] ?? 0));
            $this->info('Referência dos manuais: ' . ($resultadoCustosMp['origem_manual_referencia'] ?? ''));

            $this->info('Aplicando custos MP na ficha técnica...');

            $resultadoAplicacaoMp = $produtoCustoMpService->aplicarCustosMpNaFicha($ano, $mes);

            $this->info('Itens de MP analisados: ' . ($resultadoAplicacaoMp['itens_analisados'] ?? 0));
            $this->info('Itens de MP atualizados: ' . ($resultadoAplicacaoMp['itens_atualizados'] ?? 0));
            $this->info('Itens de MP sem custo: ' . ($resultadoAplicacaoMp['total_sem_custo'] ?? 0));

            $this->info('Importando custos operacionais...');

            $resultadoCustosOp = $produtoCustoOpService->importarCustosOp($ano, $mes);

            $this->info('Custos OP importados: ' . ($resultadoCustosOp['custos_op_importados'] ?? 0));
            $this->info('Base operacional usada: ' . ($resultadoCustosOp['ano_mes_base'] ?? ''));
            $this->info('Setores sem base: ' . ($resultadoCustosOp['setores_sem_base'] ?? 0));
            $this->info('Setores sem despesa: ' . ($resultadoCustosOp['setores_sem_despesa'] ?? 0));
            $this->info('Base total OP: ' . number_format(($resultadoCustosOp['base_total'] ?? 0), 2, ',', '.'));
            $this->info('Despesa direta OP: ' . number_format(($resultadoCustosOp['despesa_direta_total'] ?? 0), 2, ',', '.'));
            $this->info('Despesa alocada OP: ' . number_format(($resultadoCustosOp['despesa_alocada_total'] ?? 0), 2, ',', '.'));
            $this->info('Despesa total OP: ' . number_format(($resultadoCustosOp['despesas_total'] ?? 0), 2, ',', '.'));

            $this->info('Aplicando custos OP na ficha técnica...');

            $resultadoAplicacaoOp = $produtoCustoOpService->aplicarCustosOpNaFicha($ano, $mes);

            $this->info('Itens de OP analisados: ' . ($resultadoAplicacaoOp['itens_op_analisados'] ?? 0));
            $this->info('Itens de OP atualizados: ' . ($resultadoAplicacaoOp['itens_op_atualizados'] ?? 0));

            $this->info('Consolidando custo da estrutura...');

            $resultadoEstrutura = $produtoCustoEstruturaService->consolidar($ano, $mes);

            $this->info('Registros consolidados: ' . ($resultadoEstrutura['registros_consolidados'] ?? 0));
            $this->info('Custo MP total: ' . number_format(($resultadoEstrutura['custo_mp_total'] ?? 0), 2, ',', '.'));
            $this->info('Custo OP total: ' . number_format(($resultadoEstrutura['custo_op_total'] ?? 0), 2, ',', '.'));
            $this->info('Custo total estrutura: ' . number_format(($resultadoEstrutura['custo_total'] ?? 0), 2, ',', '.'));

            $totalSemFicha = $resultado['total_produtos_sem_ficha'] ?? 0;

            if ($totalSemFicha > 0) {
                $this->warn("Produtos sem ficha encontrados: {$totalSemFicha}");

                if ($semEmail) {
                    $this->warn('Opção --sem-email informada. E-mail não enviado.');
                } elseif (!empty($emails)) {
                    Mail::to($emails)->send(new ProdutosSemFichaMail($resultado));

                    $this->info('E-mail enviado para: ' . implode(', ', $emails));
                } else {
                    $this->warn('Nenhum e-mail configurado para envio.');
                }
            } else {
                $this->info('Nenhuma pendência de ficha encontrada. E-mail não enviado.');
            }

            Log::info('Command ficha:custo-diario executado.', [
                'ano' => $ano,
                'mes' => $mes,
                'produtos' => $resultado['produtos'] ?? 0,
                'linhas_ficha_custo' => $resultado['linhas_ficha_custo'] ?? 0,
                'produtos_sem_ficha' => $totalSemFicha,

                'embalagem_produtos_lidos' => $resultadoEmbalagem['produtos_lidos'] ?? 0,
                'embalagem_produtos_importados' => $resultadoEmbalagem['produtos_importados'] ?? 0,
                'embalagem_total_a' => $resultadoEmbalagem['total_a'] ?? 0,
                'embalagem_total_b' => $resultadoEmbalagem['total_b'] ?? 0,
                'embalagem_total_c' => $resultadoEmbalagem['total_c'] ?? 0,

                'custos_mp_importados' => $resultadoCustosMp['custos_importados'] ?? 0,
                'custos_manuais_aplicados' => $resultadoCustosMp['custos_manuais_aplicados'] ?? 0,
                'mps_utilizadas_estrutura' => $resultadoCustosMp['mps_utilizadas_estrutura'] ?? 0,
                'mps_nao_utilizadas_estrutura' => $resultadoCustosMp['mps_nao_utilizadas_estrutura'] ?? 0,
                'origem_manual_referencia' => $resultadoCustosMp['origem_manual_referencia'] ?? null,

                'itens_mp_analisados' => $resultadoAplicacaoMp['itens_analisados'] ?? 0,
                'itens_mp_atualizados' => $resultadoAplicacaoMp['itens_atualizados'] ?? 0,
                'itens_mp_sem_custo' => $resultadoAplicacaoMp['total_sem_custo'] ?? 0,

                'custos_op_importados' => $resultadoCustosOp['custos_op_importados'] ?? 0,
                'op_ano_mes_base' => $resultadoCustosOp['ano_mes_base'] ?? null,
                'op_setores_sem_base' => $resultadoCustosOp['setores_sem_base'] ?? 0,
                'op_setores_sem_despesa' => $resultadoCustosOp['setores_sem_despesa'] ?? 0,
                'op_base_total' => $resultadoCustosOp['base_total'] ?? 0,
                'op_despesa_direta_total' => $resultadoCustosOp['despesa_direta_total'] ?? 0,
                'op_despesa_alocada_total' => $resultadoCustosOp['despesa_alocada_total'] ?? 0,
                'op_despesas_total' => $resultadoCustosOp['despesas_total'] ?? 0,

                'itens_op_analisados' => $resultadoAplicacaoOp['itens_op_analisados'] ?? 0,
                'itens_op_atualizados' => $resultadoAplicacaoOp['itens_op_atualizados'] ?? 0,

                'estrutura_registros_consolidados' => $resultadoEstrutura['registros_consolidados'] ?? 0,
                'estrutura_custo_mp_total' => $resultadoEstrutura['custo_mp_total'] ?? 0,
                'estrutura_custo_op_total' => $resultadoEstrutura['custo_op_total'] ?? 0,
                'estrutura_custo_total' => $resultadoEstrutura['custo_total'] ?? 0,

                'sem_email' => $semEmail,
                'emails' => $emails,
            ]);

            $this->info('Ficha de custo processada com sucesso.');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            Log::error('Erro no command ficha:custo-diario.', [
                'ano' => $ano,
                'mes' => $mes,
                'erro' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'sem_email' => $semEmail,
                'emails' => $emails,
            ]);

            $this->error('Erro ao processar ficha de custo.');
            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
