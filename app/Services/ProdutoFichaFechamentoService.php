<?php

namespace App\Services;

use App\Helpers\Ficha;
use App\Models\produto_ficha;
use App\Models\produto_ficha_custo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProdutoFichaFechamentoService
{
    public function executarFichaAtual(int $ano, int $mes, string $mostrar = 'N'): array
    {
        $produtos = $this->buscarProdutosFechamento($ano, $mes);

        produto_ficha::truncate();

        $dadosFicha = [];
        $produtosSemFicha = [];
        $html = '';

        foreach ($produtos as $produto) {
            $ficha = Ficha::estrutura($produto->COD_PRODUTO);

            if (empty($ficha)) {
                $produtosSemFicha[] = [
                    'cod_produto' => $produto->COD_PRODUTO,
                    'produto' => $produto->PRODUTO,
                ];
            }

            $imprimir = empty($ficha) ? 'S' : 'N';

            if ($imprimir == 'S' || $mostrar == 'S') {
                $html .= '<b><i>' . $produto->COD_PRODUTO . ' - ' . $produto->PRODUTO . '</i></b><p>';
                $html .= '<table width="100%">';
            }

            foreach ($ficha as $itemFicha) {
                $dadosFicha[] = $this->montarLinhaFichaAntiga($itemFicha);

                if ($imprimir == 'S' || $mostrar == 'S') {
                    $html .= '<tr>';
                    $html .= '<td>' . ($itemFicha->COD_COMP ?? '') . '</td>';
                    $html .= '<td>' . ($itemFicha->DESC_COMP ?? '') . '</td>';
                    $html .= '<td align="right">' . number_format(($itemFicha->QTDE_CALC ?? 0), 2, ',', '.') . '</td>';
                    $html .= '</tr>';
                }
            }

            if ($imprimir == 'S' || $mostrar == 'S') {
                $html .= '</table>';
                $html .= '<hr><br>';
            }
        }

        $this->gravarFichaAtualEmLote($dadosFicha);

        return [
            'success' => true,
            'ano' => $ano,
            'mes' => $mes,
            'produtos' => count($produtos),
            'linhas_ficha' => count($dadosFicha),
            'produtos_sem_ficha' => $produtosSemFicha,
            'total_produtos_sem_ficha' => count($produtosSemFicha),
            'html' => $html,
        ];
    }

    public function executarFichaCusto(int $ano, int $mes, ?callable $progresso = null): array
    {
        $anoMes = $ano . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT);

        // $produtos = $this->buscarProdutosFechamento($ano, $mes);
        $produtos = $this->buscarProdutosParaValidarFichaCommand($ano, $mes);
        $totalProdutos = count($produtos);

        $this->progresso($progresso, [
            'tipo' => 'produtos_inicio',
            'total' => $totalProdutos,
            'ano_mes' => $anoMes,
        ]);

        produto_ficha_custo::where('ano', $ano)
            ->where('mes', $mes)
            ->delete();

        $dadosFichaCusto = [];
        $produtosSemFicha = [];

        foreach ($produtos as $indice => $produto) {
            $contador = $indice + 1;

            $ficha = Ficha::estrutura($produto->COD_PRODUTO);

            if (empty($ficha)) {
                $produtosSemFicha[$produto->COD_PRODUTO] = [
                    'cod_produto' => $produto->COD_PRODUTO,
                    'produto' => $produto->PRODUTO,
                    'origem' => $produto->ORIGEM_VALIDACAO ?? '',
                ];
            } else {
                foreach ($ficha as $itemFicha) {
                    $dadosFichaCusto[] = $this->montarLinhaFichaCusto(
                        $itemFicha,
                        $ano,
                        $mes,
                        $anoMes
                    );
                }
            }

            $this->progresso($progresso, [
                'tipo' => 'produto',
                'atual' => $contador,
                'total' => $totalProdutos,
                'codigo' => $produto->COD_PRODUTO,
                'produto' => $produto->PRODUTO,
                'origem' => $produto->ORIGEM_VALIDACAO ?? '',
                'itens' => empty($ficha) ? 0 : count($ficha),
                'sem_ficha' => empty($ficha),
            ]);
        }

        $this->progresso($progresso, [
            'tipo' => 'produtos_fim',
        ]);

        $this->gravarFichaCustoEmLote($dadosFichaCusto, $ano, $mes, $progresso);

        return [
            'success' => true,
            'ano' => $ano,
            'mes' => $mes,
            'ano_mes' => $anoMes,
            'produtos' => count($produtos),
            'linhas_ficha_custo' => count($dadosFichaCusto),
            'produtos_sem_ficha' => $produtosSemFicha,
            'total_produtos_sem_ficha' => count($produtosSemFicha),
        ];
    }

    private function progresso(?callable $callback, array $evento): void
    {
        if ($callback) {
            $callback($evento);
        }
    }

    private function buscarProdutosFechamento(int $ano, int $mes)
    {
        $sql = $this->sqlProdutosFechamento($ano, $mes);

        return DB::connection(env('ERP_CONNECTION'))->select($sql);
    }

    private function montarLinhaFichaAntiga($itemFicha): array
    {
        return [
            'cod' => $itemFicha->COD ?? null,
            'descr' => $itemFicha->DESCR ?? null,
            'grupo' => $itemFicha->GRUPO ?? null,
            'cod_pai' => $itemFicha->COD_PAI ?? null,
            'desc_pai' => $itemFicha->DESC_PAI ?? null,
            'cod_comp' => $itemFicha->COD_COMP ?? null,
            'desc_comp' => $itemFicha->DESC_COMP ?? null,
            'tipo_comp' => $itemFicha->TIPO_COMP ?? null,
            'grupo_comp' => $itemFicha->GRUPO_COMP ?? null,
            'qtde_calc' => $itemFicha->QTDE_CALC ?? 0,
            'nivel' => $itemFicha->NIVEL ?? 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }


    private function montarLinhaFichaCusto($itemFicha, int $ano, int $mes, string $anoMes): array
    {
        $codComp = isset($itemFicha->COD_COMP)
            ? trim((string) $itemFicha->COD_COMP)
            : '';

        $tipoComp = isset($itemFicha->TIPO_COMP)
            ? trim((string) $itemFicha->TIPO_COMP)
            : '';

        $tipoCusto = $this->identificarTipoCusto($itemFicha, $codComp, $tipoComp);

        $bases = $this->calcularBasesOperacionais($itemFicha);

        return [
            'ano' => $ano,
            'mes' => $mes,
            'ano_mes' => $anoMes,

            'cod' => $itemFicha->COD ?? null,
            'descr' => $itemFicha->DESCR ?? null,
            'grupo' => $itemFicha->GRUPO ?? null,

            'cod_pai' => $itemFicha->COD_PAI ?? null,
            'desc_pai' => $itemFicha->DESC_PAI ?? null,

            'cod_comp' => $itemFicha->COD_COMP ?? null,
            'desc_comp' => $itemFicha->DESC_COMP ?? null,
            'tipo_comp' => $itemFicha->TIPO_COMP ?? null,
            'grupo_comp' => $itemFicha->GRUPO_COMP ?? null,

            'qtde_calc' => $itemFicha->QTDE_CALC ?? 0,
            'nivel' => $itemFicha->NIVEL ?? 0,

            'tipo_custo' => $tipoCusto,
            'tipo_mp' => $itemFicha->TIPO_MP ?? null,
            'custo_var' => $itemFicha->CUSTO_VAR ?? 1,

            'base_m2' => $bases['base_m2'],
            'base_m3' => $bases['base_m3'],
            'base_kg' => $bases['base_kg'],
            'base_op_qtd' => 0,
            'base_op_origem' => null,

            'custo_mp_unit' => $itemFicha->CUSTO_MP_UNIT ?? 0,
            'valor_mp' => $itemFicha->VALOR_MP ?? 0,

            'custo_op_unit' => $itemFicha->CUSTO_OP_UNIT ?? 0,
            'valor_op' => $itemFicha->VALOR_OP ?? 0,

            'origem' => 'ERP',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function identificarTipoCusto($itemFicha, string $codComp, string $tipoComp): string
    {
        if (
            isset($itemFicha->TIPO_CUSTO)
            && trim((string) $itemFicha->TIPO_CUSTO) != ''
        ) {
            return trim((string) $itemFicha->TIPO_CUSTO);
        }

        if ($tipoComp == '09') {
            return 'OPERACIONAL';
        }

        if (substr($codComp, 0, 3) == '900') {
            return 'OPERACIONAL';
        }

        return 'MATÉRIA-PRIMA';
    }

    private function gravarFichaAtualEmLote(array $dadosFicha): void
    {
        foreach (array_chunk($dadosFicha, 500) as $lote) {
            if (!empty($lote)) {
                produto_ficha::insert($lote);
            }
        }
    }

    private function gravarFichaCustoEmLote(array $dadosFichaCusto, int $ano, int $mes, ?callable $progresso = null): void
    {
        try {
            $lotes = array_chunk($dadosFichaCusto, 500);
            $totalLotes = count($lotes);

            $this->progresso($progresso, [
                'tipo' => 'lotes_inicio',
                'total' => $totalLotes,
                'linhas' => count($dadosFichaCusto),
            ]);

            foreach ($lotes as $indice => $lote) {
                if (!empty($lote)) {
                    produto_ficha_custo::insert($lote);

                    $this->progresso($progresso, [
                        'tipo' => 'lote',
                        'atual' => $indice + 1,
                        'total' => $totalLotes,
                        'linhas' => count($lote),
                    ]);
                }
            }

            $this->progresso($progresso, [
                'tipo' => 'lotes_fim',
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao gravar produto_ficha_custo_mysql', [
                'ano' => $ano,
                'mes' => $mes,
                'erro' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function sqlProdutosFechamento(int $ano, int $mes): string
    {
        $dataInicio = sprintf('%04d-%02d-01', $ano, $mes);
        $dataFim = date('Y-m-d', strtotime($dataInicio . ' +1 month'));

        return "
            SELECT
                vendas.COD_PRODUTO,
                vendas.PRODUTO
            FROM (
                SELECT
                    ITENS.PRD_CODIGO AS COD_PRODUTO,
                    ITENS.NI_DESCRICAO AS PRODUTO
                FROM NF_VENDA_CABECALHO NF
                LEFT JOIN NF_VENDA_ITENS ITENS
                    ON ITENS.NF_NUMERO = NF.NF_NUMERO
                LEFT JOIN CFOP
                    ON CFOP.CFOP_CODIGO_NF = ITENS.CFOP_CODIGO_NF
                LEFT JOIN CFOP_GRUPOS
                    ON CFOP_GRUPOS.CFGR_CODIGO = CFOP.CFGR_CODIGO
                LEFT JOIN DAV
                    ON DAV.DAV_NUMERO = ITENS.PED_NUMERO
                WHERE 1 = 1
                AND ITENS.PRD_CODIGO IS NOT NULL
                AND (CFOP_GRUPOS.CFGR_CODIGO = 1 OR RIGHT(TRIM(ITENS.CFOP_CODIGO), 3) = '124')
                AND NF.NF_SITUACAO IN (1)
                AND DAV.DAV_NUMERO IS NOT NULL
                AND COALESCE(DAV.DAV_SITUACAO_ENTREGA, 0) <> 1
                AND NF.NF_DT_EMISSAO >= CAST('$dataInicio' AS DATE)
                AND NF.NF_DT_EMISSAO < CAST('$dataFim' AS DATE)

                UNION ALL

                SELECT
                    ITENS.PRD_CODIGO AS COD_PRODUTO,
                    ITENS.NI_DESCRICAO AS PRODUTO
                FROM NF_VENDA_CABECALHO NF
                LEFT JOIN NF_VENDA_ITENS ITENS
                    ON ITENS.NF_NUMERO = NF.NF_NUMERO
                LEFT JOIN CFOP
                    ON CFOP.CFOP_CODIGO_NF = ITENS.CFOP_CODIGO_NF
                LEFT JOIN CFOP_GRUPOS
                    ON CFOP_GRUPOS.CFGR_CODIGO = CFOP.CFGR_CODIGO
                LEFT JOIN DAV
                    ON DAV.DAV_NUMERO = ITENS.PED_NUMERO
                WHERE 1 = 1
                AND ITENS.PRD_CODIGO IS NOT NULL
                AND (CFOP_GRUPOS.CFGR_CODIGO = 1 OR RIGHT(TRIM(ITENS.CFOP_CODIGO), 3) = '124')
                AND NF.NF_SITUACAO IN (1)
                AND DAV.DAV_NUMERO IS NULL
                AND COALESCE(DAV.DAV_SITUACAO_ENTREGA, 0) <> 1
                AND NF.NF_DT_EMISSAO >= CAST('$dataInicio' AS DATE)
                AND NF.NF_DT_EMISSAO < CAST('$dataFim' AS DATE)

                UNION ALL

                SELECT
                    ITENS.PRD_CODIGO AS COD_PRODUTO,
                    ITENS.NI_DESCRICAO AS PRODUTO
                FROM NF_VENDA_CABECALHO NF
                LEFT JOIN NF_VENDA_ITENS ITENS
                    ON ITENS.NF_NUMERO = NF.NF_NUMERO
                LEFT JOIN CFOP
                    ON CFOP.CFOP_CODIGO_NF = ITENS.CFOP_CODIGO_NF
                LEFT JOIN CFOP_GRUPOS
                    ON CFOP_GRUPOS.CFGR_CODIGO = CFOP.CFGR_CODIGO
                LEFT JOIN DAV
                    ON DAV.DAV_NUMERO = ITENS.PED_NUMERO
                WHERE 1 = 1
                AND ITENS.PRD_CODIGO IS NOT NULL
                AND (CFOP_GRUPOS.CFGR_CODIGO = 1 OR RIGHT(TRIM(ITENS.CFOP_CODIGO), 3) = '124')
                AND NF.NF_SITUACAO IN (1)
                AND DAV.DAV_NUMERO IS NOT NULL
                AND COALESCE(DAV.DAV_SITUACAO_ENTREGA, 1) = 1
                AND NF.NF_DT_EMISSAO >= CAST('$dataInicio' AS DATE)
                AND NF.NF_DT_EMISSAO < CAST('$dataFim' AS DATE)

                UNION ALL

                SELECT
                    DAV_ITENS.PRD_CODIGO AS COD_PRODUTO,
                    DAV_ITENS.DAVI_DESCRICAO AS PRODUTO
                FROM NF_VENDA_CABECALHO NF
                LEFT JOIN NF_VENDA_ITENS ITENS
                    ON ITENS.NF_NUMERO = NF.NF_NUMERO
                LEFT JOIN CFOP
                    ON CFOP.CFOP_CODIGO_NF = ITENS.CFOP_CODIGO_NF
                LEFT JOIN CFOP_GRUPOS
                    ON CFOP_GRUPOS.CFGR_CODIGO = CFOP.CFGR_CODIGO
                LEFT JOIN DAV
                    ON DAV.DAV_NUMERO = ITENS.PED_NUMERO
                LEFT JOIN DAV_ITENS
                    ON DAV_ITENS.DAV_NUMERO = DAV.DAV_NUMERO
                    AND DAV_ITENS.DAVI_QTDE_ENTREGA = 0
                WHERE 1 = 1
                AND DAV_ITENS.PRD_CODIGO IS NOT NULL
                AND (CFOP_GRUPOS.CFGR_CODIGO = 1 OR RIGHT(TRIM(ITENS.CFOP_CODIGO), 3) = '124')
                AND NF.NF_SITUACAO IN (1)
                AND COALESCE(DAV.DAV_SITUACAO_ENTREGA, 1) = 1
                AND DAV.DAV_NUMERO IS NOT NULL
                AND NF.NF_DT_EMISSAO >= CAST('$dataInicio' AS DATE)
                AND NF.NF_DT_EMISSAO < CAST('$dataFim' AS DATE)

                UNION ALL

                SELECT
                    DAV_ITENS.PRD_CODIGO AS COD_PRODUTO,
                    DAV_ITENS.DAVI_DESCRICAO AS PRODUTO
                FROM DAV
                LEFT JOIN DAV_ITENS
                    ON DAV_ITENS.DAV_NUMERO = DAV.DAV_NUMERO
                WHERE 1 = 1
                AND DAV_ITENS.PRD_CODIGO IS NOT NULL
                AND DAV.DSP_CODIGO = 1
                AND DAV.DAV_DATA_EMISSAO >= CAST('$dataInicio' AS DATE)
                AND DAV.DAV_DATA_EMISSAO < CAST('$dataFim' AS DATE)

            ) vendas

            LEFT JOIN PRODUTOS
                ON PRODUTOS.PRD_CODIGO = vendas.COD_PRODUTO

            WHERE 1 = 1
            AND vendas.COD_PRODUTO IS NOT NULL
            AND PRODUTOS.PRD_TIPO_PRODUTO IN ('04')

            GROUP BY
                vendas.COD_PRODUTO,
                vendas.PRODUTO
        ";
    }

    private function buscarProdutosParaValidarFichaCommand(int $ano, int $mes): array
    {
        $produtosFechamento = $this->buscarProdutosFechamento($ano, $mes);
        $produtosDavItens = $this->buscarProdutosDavItens($ano, $mes);

        $produtos = [];

        foreach ($produtosFechamento as $produto) {
            $codigo = trim((string) ($produto->COD_PRODUTO ?? ''));

            if ($codigo == '') {
                continue;
            }

            $produtos[$codigo] = (object) [
                'COD_PRODUTO' => $codigo,
                'PRODUTO' => $produto->PRODUTO ?? '',
                'ORIGEM_VALIDACAO' => 'FECHAMENTO',
            ];
        }

        foreach ($produtosDavItens as $produto) {
            $codigo = trim((string) ($produto->COD_PRODUTO ?? ''));

            if ($codigo == '') {
                continue;
            }

            if (!isset($produtos[$codigo])) {
                $produtos[$codigo] = (object) [
                    'COD_PRODUTO' => $codigo,
                    'PRODUTO' => $produto->PRODUTO ?? '',
                    'ORIGEM_VALIDACAO' => 'DAV_ITENS',
                ];
            } else {
                $origemAtual = $produtos[$codigo]->ORIGEM_VALIDACAO ?? '';

                if (strpos($origemAtual, 'DAV_ITENS') === false) {
                    $produtos[$codigo]->ORIGEM_VALIDACAO = $origemAtual . '+DAV_ITENS';
                }
            }
        }

        return array_values($produtos);
    }

    private function buscarProdutosDavItens(int $ano, int $mes): array
    {
        $dataInicio = sprintf('%04d-%02d-01', $ano, $mes);
        $dataFim = date('Y-m-d', strtotime($dataInicio . ' +1 month'));

        $sql = "
            SELECT
                DAV_ITENS.PRD_CODIGO AS COD_PRODUTO,
                MAX(DAV_ITENS.DAVI_DESCRICAO) AS PRODUTO,
                COUNT(*) AS TOTAL_ITENS
            FROM DAV
            INNER JOIN DAV_ITENS
                ON DAV_ITENS.DAV_NUMERO = DAV.DAV_NUMERO
            INNER JOIN PRODUTOS
                ON PRODUTOS.PRD_CODIGO = DAV_ITENS.PRD_CODIGO
            WHERE 1 = 1
            AND DAV_ITENS.PRD_CODIGO IS NOT NULL
            AND DAV.DSP_CODIGO = 1
            AND PRODUTOS.PRD_TIPO_PRODUTO IN ('04')
            AND DAV.DAV_DATA_EMISSAO >= CAST(? AS DATE)
            AND DAV.DAV_DATA_EMISSAO < CAST(? AS DATE)
            GROUP BY
                DAV_ITENS.PRD_CODIGO
        ";

        return DB::connection(env('ERP_CONNECTION', 'Decorbras'))->select($sql, [
            $dataInicio,
            $dataFim,
        ]);
    }

    private function normalizarNumeroFicha($valor): float
    {
        if ($valor === null || $valor === '') {
            return 0;
        }

        if (is_numeric($valor)) {
            return (float) $valor;
        }

        $valor = trim((string) $valor);
        $valor = str_replace(' ', '', $valor);

        if (strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return is_numeric($valor)
            ? (float) $valor
            : 0;
    }

    private function calcularBasesOperacionais($itemFicha): array
    {
        /*
        * A Ficha::estrutura() não traz as colunas AS/AT da planilha.
        * Então calculamos M2 e M3 pela descrição do produto acabado.
        *
        * Exemplo:
        * HDF PINUS CAT. B 2730X1830X04MM 2F A/A
        *
        * base_m2 = 2.730 * 1.830
        * base_m3 = base_m2 * 0.004
        */

        $descricaoProduto = isset($itemFicha->DESCR)
            ? trim((string) $itemFicha->DESCR)
            : '';

        $dimensoes = $this->extrairDimensoesProduto($descricaoProduto);

        return [
            'base_m2' => $dimensoes['base_m2'],
            'base_m3' => $dimensoes['base_m3'],
            'base_kg' => 0,
        ];
    }

    private function extrairDimensoesProduto(string $descricao): array
    {
        $baseM2 = 0;
        $baseM3 = 0;

        $texto = strtoupper($descricao);
        $texto = str_replace(',', '.', $texto);
        $texto = preg_replace('/\s+/', ' ', $texto);

        /*
        * Caso 1:
        * Painéis com três dimensões:
        *
        * 2730X1830X04MM
        * 2750X1600X04MM
        * 3080X1250X0.8MM
        * 3080 X 1250 X 0.8 MM
        */
        $padraoTresDimensoes = '/(\d+(?:\.\d+)?)\s*[X]\s*(\d+(?:\.\d+)?)\s*[X]\s*(\d+(?:\.\d+)?)\s*MM/';

        if (preg_match($padraoTresDimensoes, $texto, $matches)) {
            $comprimentoMm = $this->normalizarNumeroFicha($matches[1]);
            $larguraMm = $this->normalizarNumeroFicha($matches[2]);
            $espessuraMm = $this->normalizarNumeroFicha($matches[3]);

            if ($comprimentoMm > 0 && $larguraMm > 0) {
                $comprimentoM = $comprimentoMm / 1000;
                $larguraM = $larguraMm / 1000;

                $baseM2 = $comprimentoM * $larguraM;

                if ($espessuraMm > 0) {
                    $espessuraM = $espessuraMm / 1000;
                    $baseM3 = $baseM2 * $espessuraM;
                }
            }

            return [
                'base_m2' => $baseM2,
                'base_m3' => $baseM3,
            ];
        }

        /*
        * Caso 2:
        * Lâmina de borda / fitas:
        *
        * 40MM C/250MT
        * 20MM C/200MT
        * 45MM C/250MT
        *
        * base_m2 = largura_mm / 1000 * comprimento_mt
        */
        $padraoBorda = '/(\d+(?:\.\d+)?)\s*MM\s*C\/\s*(\d+(?:\.\d+)?)\s*MT/';

        if (preg_match($padraoBorda, $texto, $matches)) {
            $larguraMm = $this->normalizarNumeroFicha($matches[1]);
            $comprimentoMt = $this->normalizarNumeroFicha($matches[2]);

            if ($larguraMm > 0 && $comprimentoMt > 0) {
                $baseM2 = ($larguraMm / 1000) * $comprimentoMt;
            }

            return [
                'base_m2' => $baseM2,
                'base_m3' => 0,
            ];
        }

        return [
            'base_m2' => 0,
            'base_m3' => 0,
        ];
    }


}
