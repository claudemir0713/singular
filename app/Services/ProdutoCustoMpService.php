<?php

namespace App\Services;

use App\Models\produto_custos_mp;
use App\Models\produto_ficha_custo;
use Illuminate\Support\Facades\DB;

class ProdutoCustoMpService
{
    private const CREDITO_IMP_PADRAO = 0.2125;

    public function importarCustosMpErp(int $ano, int $mes): array
    {
        $anoMes = $ano . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT);

        $codigosUtilizados = DB::table('produto_ficha_custo_mysql')
            ->where('ano', $ano)
            ->where('mes', $mes)
            ->where('tipo_custo', 'MATÉRIA-PRIMA')
            ->whereNotNull('cod_comp')
            ->distinct()
            ->pluck('cod_comp')
            ->map(function ($codigo) {
                return trim((string) $codigo);
            })
            ->filter()
            ->values()
            ->all();

        $codigosUtilizadosMap = array_flip($codigosUtilizados);

        $custosErp = $this->buscarCustosMpErp($codigosUtilizados);

        $custosManuais = $this->buscarCustosManuaisDoPeriodo($ano, $mes);
        $origemManualReferencia = 'MES_ATUAL';

        if ($custosManuais->isEmpty()) {
            [$anoAnterior, $mesAnterior] = $this->periodoAnterior($ano, $mes);

            $custosManuais = $this->buscarCustosManuaisDoPeriodo($anoAnterior, $mesAnterior);
            $origemManualReferencia = 'MES_ANTERIOR';
        }

        produto_custos_mp::where('ano', $ano)
            ->where('mes', $mes)
            ->delete();

        $dados = [];

        foreach ($custosErp as $item) {
            $codMp = trim((string) ($item->COD_PROD ?? ''));

            if ($codMp == '') {
                continue;
            }

            $descMp = $item->DESC_PROD ?? null;
            $unitSistema = (float) ($item->VALOR_UNIT ?? 0);

            $manual = $custosManuais[$codMp] ?? null;

            $unitManual = $manual ? (float) $manual->unit_manual : 0;

            $creditoImp = $manual
                ? (float) $manual->credito_imp
                : self::CREDITO_IMP_PADRAO;

            $valorUnit = $unitManual > 0
                ? $unitManual
                : $unitSistema;

            $diferenca = $unitManual > 0
                ? $unitManual - $unitSistema
                : 0;

            $custoLiquido = $valorUnit * (1 - $creditoImp);

            $temManual = ($unitManual > 0 || $creditoImp > 0);

            $utilizadaEstrutura = isset($codigosUtilizadosMap[$codMp]) ? 'S' : 'N';

            $dados[] = [
                'ano' => $ano,
                'mes' => $mes,
                'ano_mes' => $anoMes,

                'cod_mp' => $codMp,
                'desc_mp' => $descMp,

                'unid_med' => $manual ? $manual->unid_med : null,
                'tipo' => $manual ? $manual->tipo : null,

                'unit_manual' => $unitManual,
                'unit_sistema' => $unitSistema,
                'diferenca' => $diferenca,

                'valor_unit' => $valorUnit,
                'credito_imp' => $creditoImp,
                'custo_liquido' => $custoLiquido,
                'custo_ok' => $custoLiquido,

                'utilizada_estrutura' => $utilizadaEstrutura,

                'origem' => $temManual ? 'MANUAL+ERP' : 'ERP',

                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($dados, 500) as $lote) {
            if (!empty($lote)) {
                produto_custos_mp::insert($lote);
            }
        }

        return [
            'ano' => $ano,
            'mes' => $mes,
            'ano_mes' => $anoMes,

            'custos_importados' => count($dados),

            'custos_manuais_aplicados' => collect($dados)->filter(function ($item) {
                return ($item['unit_manual'] > 0 || $item['credito_imp'] > 0);
            })->count(),

            'mps_utilizadas_estrutura' => collect($dados)->where('utilizada_estrutura', 'S')->count(),

            'mps_nao_utilizadas_estrutura' => collect($dados)->where('utilizada_estrutura', 'N')->count(),

            'origem_manual_referencia' => $origemManualReferencia,
        ];
    }

    public function aplicarCustosMpNaFicha(int $ano, int $mes): array
    {
        $anoMes = $ano . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT);

        $itensAnalisados = produto_ficha_custo::where('ano', $ano)
            ->where('mes', $mes)
            ->where('tipo_custo', 'MATÉRIA-PRIMA')
            ->count();

        $sql = "
            UPDATE produto_ficha_custo_mysql f

            INNER JOIN produto_custos_mp_mysql c
                ON c.ano = f.ano
                AND c.mes = f.mes
                AND c.cod_mp = f.cod_comp

            LEFT JOIN produto_embalagem_mysql e
                ON e.cod_prod = f.cod

            SET
                f.tipo_mp = c.tipo,

                f.custo_var =
                    CASE
                        WHEN COALESCE(c.tipo, 'N') = 'CV'
                        THEN COALESCE(e.custo_percentual, 1)
                        ELSE 1
                    END,

                f.custo_mp_unit = c.custo_ok,

                f.valor_mp =
                    COALESCE(f.qtde_calc, 0)
                    * c.custo_ok
                    * CASE
                        WHEN COALESCE(c.tipo, 'N') = 'CV'
                        THEN COALESCE(e.custo_percentual, 1)
                        ELSE 1
                      END,

                f.updated_at = NOW()

            WHERE f.ano = ?
            AND f.mes = ?
            AND f.tipo_custo = 'MATÉRIA-PRIMA'
        ";

        $itensAtualizados = DB::affectingStatement($sql, [
            $ano,
            $mes,
        ]);

        $semCusto = produto_ficha_custo::where('ano', $ano)
            ->where('mes', $mes)
            ->where('tipo_custo', 'MATÉRIA-PRIMA')
            ->where(function ($query) {
                $query->whereNull('custo_mp_unit')
                    ->orWhere('custo_mp_unit', 0);
            })
            ->select('cod_comp', 'desc_comp', 'cod', 'descr')
            ->get()
            ->map(function ($item) {
                return [
                    'cod_comp' => $item->cod_comp,
                    'desc_comp' => $item->desc_comp,
                    'cod_produto' => $item->cod,
                    'produto' => $item->descr,
                ];
            })
            ->toArray();

        return [
            'ano' => $ano,
            'mes' => $mes,
            'ano_mes' => $anoMes,

            'itens_analisados' => $itensAnalisados,
            'itens_atualizados' => $itensAtualizados,

            'itens_sem_custo' => array_values($semCusto),
            'total_sem_custo' => count($semCusto),
        ];
    }

    public function salvarCustoManual(array $dados): produto_custos_mp
    {
        $ano = (int) ($dados['ano'] ?? date('Y'));
        $mes = (int) ($dados['mes'] ?? date('m'));

        $codMp = trim((string) ($dados['cod_mp'] ?? ''));

        if ($codMp == '') {
            throw new \Exception('Código da matéria-prima não informado.');
        }

        $custo = produto_custos_mp::where('ano', $ano)
            ->where('mes', $mes)
            ->where('cod_mp', $codMp)
            ->first();

        if (!$custo) {
            throw new \Exception('Matéria-prima não encontrada para o período informado.');
        }

        $unitManual = $this->normalizarNumero($dados['unit_manual'] ?? 0);
        $creditoImp = $this->normalizarPercentual($dados['credito_imp'] ?? 0);

        $unidMed = strtoupper(trim((string) ($dados['unid_med'] ?? '')));
        $tipo = strtoupper(trim((string) ($dados['tipo'] ?? '')));

        $unitSistema = (float) $custo->unit_sistema;

        $valorUnit = $unitManual > 0
            ? $unitManual
            : $unitSistema;

        $diferenca = $unitManual > 0
            ? $unitManual - $unitSistema
            : 0;

        $custoLiquido = $valorUnit * (1 - $creditoImp);

        $custo->unit_manual = $unitManual;
        $custo->diferenca = $diferenca;
        $custo->valor_unit = $valorUnit;
        $custo->credito_imp = $creditoImp;
        $custo->custo_liquido = $custoLiquido;
        $custo->custo_ok = $custoLiquido;
        $custo->origem = ($unitManual > 0 || $creditoImp > 0) ? 'MANUAL+ERP' : 'ERP';

        if ($unidMed !== '') {
            $custo->unid_med = $unidMed;
        }

        if ($tipo !== '') {
            $custo->tipo = $tipo;
        }

        $custo->save();

        $linhasFichaAtualizadas = $this->atualizarFichaCustoPorMateriaPrima($custo);

        $produtosEstruturaAtualizados = $this->atualizarCustoEstruturaPorMateriaPrima($custo);

        $custo->linhas_ficha_atualizadas = $linhasFichaAtualizadas;
        $custo->produtos_estrutura_atualizados = $produtosEstruturaAtualizados;

        return $custo;
    }

    public function importarCustosManuaisTexto(int $ano, int $mes, string $texto): array
    {
        $linhas = preg_split('/\r\n|\r|\n/', trim($texto));

        $dadosPorCodigo = [];

        $linhasLidas = 0;
        $linhasIgnoradas = 0;

        foreach ($linhas as $linha) {
            $linha = trim($linha);

            if ($linha == '') {
                continue;
            }

            if (
                stripos($linha, 'CÓDIGO') !== false ||
                stripos($linha, 'CODIGO') !== false
            ) {
                continue;
            }

            $partes = preg_split('/\t+|;/', $linha);

            if (count($partes) < 4) {
                $linhasIgnoradas++;
                continue;
            }

            $codigo = trim((string) ($partes[0] ?? ''));
            $descricao = trim((string) ($partes[1] ?? ''));

            $unidMed = '';
            $tipo = '';
            $valorUnitTexto = '';
            $creditoImpTexto = '';

            if (count($partes) >= 6) {
                $unidMed = strtoupper(trim((string) ($partes[2] ?? '')));
                $tipo = strtoupper(trim((string) ($partes[3] ?? '')));
                $valorUnitTexto = trim((string) ($partes[4] ?? ''));
                $creditoImpTexto = trim((string) ($partes[5] ?? ''));
            } else {
                $valorUnitTexto = trim((string) ($partes[2] ?? ''));
                $creditoImpTexto = trim((string) ($partes[3] ?? ''));
            }

            if ($codigo == '') {
                $linhasIgnoradas++;
                continue;
            }

            $unitManual = $this->normalizarNumero($valorUnitTexto);
            $creditoImpNormalizado = $this->normalizarPercentualTexto($creditoImpTexto);

            if (
                $unitManual <= 0 &&
                $creditoImpNormalizado <= 0 &&
                $unidMed == '' &&
                $tipo == ''
            ) {
                $linhasIgnoradas++;
                continue;
            }

            $linhasLidas++;

            $dadosPorCodigo[$codigo] = [
                'cod_mp' => $codigo,
                'desc_mp' => $descricao,
                'unid_med' => $unidMed,
                'tipo' => $tipo,
                'unit_manual' => $unitManual,
                'credito_imp' => $creditoImpNormalizado,
            ];
        }

        $atualizados = 0;
        $valoresCustoAtualizados = 0;
        $unidTipoAtualizados = 0;
        $naoEncontrados = [];

        foreach ($dadosPorCodigo as $codigo => $dados) {
            $custo = produto_custos_mp::where('ano', $ano)
                ->where('mes', $mes)
                ->where('cod_mp', $codigo)
                ->first();

            if (!$custo) {
                $naoEncontrados[] = $dados;
                continue;
            }

            $unitSistema = (float) $custo->unit_sistema;
            $unitManual = (float) $dados['unit_manual'];
            $creditoImp = (float) $dados['credito_imp'];

            if ($unitManual > 0 || $creditoImp > 0) {
                $valorUnit = $unitManual > 0
                    ? $unitManual
                    : $unitSistema;

                $diferenca = $unitManual > 0
                    ? $unitManual - $unitSistema
                    : 0;

                $custoLiquido = $valorUnit * (1 - $creditoImp);

                $custo->unit_manual = $unitManual;
                $custo->diferenca = $diferenca;
                $custo->valor_unit = $valorUnit;
                $custo->credito_imp = $creditoImp;
                $custo->custo_liquido = $custoLiquido;
                $custo->custo_ok = $custoLiquido;
                $custo->origem = ($unitManual > 0 || $creditoImp > 0) ? 'MANUAL+ERP' : 'ERP';

                $valoresCustoAtualizados++;
            }

            if (($dados['unid_med'] ?? '') !== '') {
                $custo->unid_med = $dados['unid_med'];
            }

            if (($dados['tipo'] ?? '') !== '') {
                $custo->tipo = $dados['tipo'];
            }

            if (($dados['unid_med'] ?? '') !== '' || ($dados['tipo'] ?? '') !== '') {
                $unidTipoAtualizados++;
            }

            $custo->save();

            $atualizados++;
        }

        return [
            'ano' => $ano,
            'mes' => $mes,
            'ano_mes' => $ano . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT),

            'linhas_lidas' => $linhasLidas,
            'linhas_ignoradas' => $linhasIgnoradas,
            'codigos_identificados' => count($dadosPorCodigo),

            'custos_atualizados' => $atualizados,
            'valores_custo_atualizados' => $valoresCustoAtualizados,
            'unid_tipo_atualizados' => $unidTipoAtualizados,

            'nao_encontrados' => array_values($naoEncontrados),
            'total_nao_encontrados' => count($naoEncontrados),
        ];
    }

    // private function buscarCustosMpErp(): array
    // {
    //     $sql = "
    //         SELECT
    //             PRD_CODIGO AS COD_PROD,
    //             PRD_DESCRICAO AS DESC_PROD,
    //             PRD_PRECO_CUSTO AS VALOR_UNIT,
    //             '' AS CONF
    //         FROM PRODUTOS
    //         WHERE 1 = 1
    //         AND PRD_TIPO_PRODUTO IN ('01')
    //         ORDER BY PRD_CODIGO
    //     ";

    //     return DB::connection(env('ERP_CONNECTION', 'Decorbras'))->select($sql);
    // }

    private function buscarCustosMpErp(array $codigosUtilizados = []): array
    {
        $sql = "
            SELECT
                PRD_CODIGO AS COD_PROD,
                PRD_DESCRICAO AS DESC_PROD,
                PRD_PRECO_CUSTO AS VALOR_UNIT,
                '' AS CONF
            FROM PRODUTOS
            WHERE 1 = 1
            AND PRD_TIPO_PRODUTO IN ('01')
            ORDER BY PRD_CODIGO
        ";

        $dados = DB::connection(env('ERP_CONNECTION', 'Decorbras'))->select($sql);

        $mapa = [];

        foreach ($dados as $item) {
            $codProd = trim((string) ($item->COD_PROD ?? ''));

            if ($codProd == '') {
                continue;
            }

            $mapa[$codProd] = $item;
        }

        $codigosUtilizados = collect($codigosUtilizados)
            ->map(function ($codigo) {
                return trim((string) $codigo);
            })
            ->filter()
            ->unique()
            ->values()
            ->all();

        foreach (array_chunk($codigosUtilizados, 500) as $loteCodigos) {
            if (empty($loteCodigos)) {
                continue;
            }

            $placeholders = implode(',', array_fill(0, count($loteCodigos), '?'));

            $sqlComplementar = "
                SELECT
                    PRD_CODIGO AS COD_PROD,
                    PRD_DESCRICAO AS DESC_PROD,
                    PRD_PRECO_CUSTO AS VALOR_UNIT,
                    '' AS CONF
                FROM PRODUTOS
                WHERE PRD_CODIGO IN ({$placeholders})
                ORDER BY PRD_CODIGO
            ";

            $complementares = DB::connection(env('ERP_CONNECTION', 'Decorbras'))
                ->select($sqlComplementar, $loteCodigos);

            foreach ($complementares as $item) {
                $codProd = trim((string) ($item->COD_PROD ?? ''));

                if ($codProd == '') {
                    continue;
                }

                $mapa[$codProd] = $item;
            }
        }

        return array_values($mapa);
    }

    private function buscarCustosManuaisDoPeriodo(int $ano, int $mes)
    {
        return produto_custos_mp::where('ano', $ano)
            ->where('mes', $mes)
            ->where(function ($query) {
                $query->where('unit_manual', '>', 0)
                    ->orWhere('credito_imp', '>', 0)
                    ->orWhereNotNull('unid_med')
                    ->orWhereNotNull('tipo')
                    ->orWhere('unid_med', '<>', '')
                    ->orWhere('tipo', '<>', '');
            })
            ->get()
            ->keyBy(function ($item) {
                return trim((string) $item->cod_mp);
            });
    }

    private function periodoAnterior(int $ano, int $mes): array
    {
        if ($mes == 1) {
            return [$ano - 1, 12];
        }

        return [$ano, $mes - 1];
    }

    private function normalizarNumero($valor): float
    {
        if ($valor === null || $valor === '') {
            return 0;
        }

        $valor = trim((string) $valor);
        $valor = str_replace('R$', '', $valor);
        $valor = str_replace(' ', '', $valor);

        if (strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return is_numeric($valor) ? (float) $valor : 0;
    }

    private function normalizarPercentual($valor): float
    {
        $numero = $this->normalizarNumero($valor);

        if ($numero > 1) {
            return $numero / 100;
        }

        return $numero;
    }

    private function normalizarPercentualTexto($valor): float
    {
        if ($valor === null || $valor === '') {
            return 0;
        }

        $valor = str_replace('%', '', (string) $valor);

        return $this->normalizarPercentual($valor);
    }

    private function atualizarFichaCustoPorMateriaPrima(produto_custos_mp $custo): int
    {
        $ano = (int) $custo->ano;
        $mes = (int) $custo->mes;
        $codMp = trim((string) $custo->cod_mp);

        $sql = "
            UPDATE produto_ficha_custo_mysql f

            INNER JOIN produto_custos_mp_mysql c
                ON c.ano = f.ano
                AND c.mes = f.mes
                AND c.cod_mp = f.cod_comp

            LEFT JOIN produto_embalagem_mysql e
                ON e.cod_prod = f.cod

            SET
                f.tipo_mp = c.tipo,

                f.custo_var =
                    CASE
                        WHEN COALESCE(c.tipo, 'N') = 'CV'
                        THEN COALESCE(e.custo_percentual, 1)
                        ELSE 1
                    END,

                f.custo_mp_unit = c.custo_ok,

                f.valor_mp =
                    COALESCE(f.qtde_calc, 0)
                    * c.custo_ok
                    * CASE
                        WHEN COALESCE(c.tipo, 'N') = 'CV'
                        THEN COALESCE(e.custo_percentual, 1)
                        ELSE 1
                      END,

                f.updated_at = NOW()

            WHERE f.ano = ?
            AND f.mes = ?
            AND f.tipo_custo = 'MATÉRIA-PRIMA'
            AND f.cod_comp = ?
        ";

        return DB::affectingStatement($sql, [
            $ano,
            $mes,
            $codMp,
        ]);
    }

    private function atualizarCustoEstruturaPorMateriaPrima(produto_custos_mp $custo): int
    {
        $ano = (int) $custo->ano;
        $mes = (int) $custo->mes;

        $produtosAfetados = produto_ficha_custo::where('ano', $ano)
            ->where('mes', $mes)
            ->where('tipo_custo', 'MATÉRIA-PRIMA')
            ->where('cod_comp', trim((string) $custo->cod_mp))
            ->select('cod')
            ->distinct()
            ->pluck('cod')
            ->map(function ($codigo) {
                return trim((string) $codigo);
            })
            ->filter()
            ->values()
            ->all();

        app(ProdutoCustoEstruturaService::class)->consolidar($ano, $mes);

        return count($produtosAfetados);
    }
}
