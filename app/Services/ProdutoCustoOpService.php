<?php

namespace App\Services;

use App\Models\produto_custo_op_depara;
use App\Models\produto_custos_op;
use App\Models\produto_ficha_custo;
use Illuminate\Support\Facades\DB;

class ProdutoCustoOpService
{
    private const MLC_CONNECTION = 'mlc';
    private const EMPRESA_MLC = 371;

    public function importarCustosOp(int $ano, int $mes): array
    {
        $anoMes = $ano . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT);

        list($anoBase, $mesBase) = $this->periodoBaseOperacional($ano, $mes);

        $anoMesBase = $anoBase . '-' . str_pad($mesBase, 2, '0', STR_PAD_LEFT);

        $despesasPorSetor = $this->buscarDespesasPorSetor($anoBase, $mesBase);
        $basePorSetor = $this->buscarBaseOperacionalPorSetor($anoBase, $mesBase);

        $deparas = produto_custo_op_depara::where('ativo', 'S')
            ->orderBy('cod_cc')
            ->get();

        produto_custos_op::where('ano', $ano)
            ->where('mes', $mes)
            ->delete();

        $registros = [];
        $totalSemBase = 0;
        $totalSemDespesa = 0;

        foreach ($deparas as $depara) {
            $codCc = (int) $depara->cod_cc;
            $codContaHora = (int) $depara->cod_contahora;

            $chaveDespesa = trim((string) $codCc);
            $chaveBase = trim((string) $codCc);

            $despesa = $despesasPorSetor[$chaveDespesa] ?? null;
            $base = $basePorSetor[$chaveBase] ?? null;

            $despesaDireta = $despesa ? (float) $despesa->despesa_direta : 0;
            $despesaAlocada = $despesa ? (float) $despesa->despesa_alocada : 0;
            $despesas = $despesaDireta + $despesaAlocada;

            $baseQtd = $base ? (float) $base->base_qtd : 0;

            if ($baseQtd <= 0) {
                $totalSemBase++;
            }

            if ($despesas <= 0) {
                $totalSemDespesa++;
            }

            $custoOpUnit = $baseQtd > 0 ? $despesas / $baseQtd : 0;

            $registros[] = [
                'ano' => $ano,
                'mes' => $mes,
                'ano_mes' => $anoMes,

                'ano_base' => $anoBase,
                'mes_base' => $mesBase,
                'ano_mes_base' => $anoMesBase,

                'cod_cc' => $codCc,
                'centro_custo' => $depara->centro_custo,

                'cod_processo' => $depara->cod_processo,
                'desc_processo' => $depara->desc_processo,

                'cod_contahora' => $codContaHora,
                'contahora' => $depara->contahora,

                'unid_med' => $depara->unid_med,

                'base_qtd' => $baseQtd,

                'despesa_direta' => $despesaDireta,
                'despesa_alocada' => $despesaAlocada,
                'despesas' => $despesas,

                'custo_op_unit' => $custoOpUnit,

                'origem' => 'MLC_DECORBRAS',

                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($registros, 500) as $lote) {
            if (!empty($lote)) {
                produto_custos_op::insert($lote);
            }
        }

        return [
            'ano' => $ano,
            'mes' => $mes,
            'ano_mes' => $anoMes,

            'ano_base' => $anoBase,
            'mes_base' => $mesBase,
            'ano_mes_base' => $anoMesBase,

            'custos_op_importados' => count($registros),
            'setores_sem_base' => $totalSemBase,
            'setores_sem_despesa' => $totalSemDespesa,

            'base_total' => collect($registros)->sum('base_qtd'),
            'despesa_direta_total' => collect($registros)->sum('despesa_direta'),
            'despesa_alocada_total' => collect($registros)->sum('despesa_alocada'),
            'despesas_total' => collect($registros)->sum('despesas'),
        ];
    }

    public function aplicarCustosOpNaFicha(int $ano, int $mes): array
    {
        $anoMes = $ano . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT);

        $itensAnalisados = produto_ficha_custo::where('ano', $ano)
            ->where('mes', $mes)
            ->whereIn('cod_comp', function ($query) use ($ano, $mes) {
                $query->select('cod_processo')
                    ->from('produto_custos_op_mysql')
                    ->where('ano', $ano)
                    ->where('mes', $mes);
            })
            ->count();

        $sql = "
            UPDATE produto_ficha_custo_mysql f

            INNER JOIN produto_custos_op_mysql c
                ON c.ano = f.ano
                AND c.mes = f.mes
                AND c.cod_processo = f.cod_comp

            LEFT JOIN produto_custo_op_depara_mysql d
                ON d.cod_processo = c.cod_processo
                AND d.ativo = 'S'

            LEFT JOIN produto_ficha_custo_mysql aux
                ON aux.ano = f.ano
                AND aux.mes = f.mes
                AND aux.cod = f.cod
                AND aux.cod_comp = d.cod_comp_aux

            SET
                f.tipo_custo = 'OPERACIONAL',
                f.custo_op_unit = c.custo_op_unit,

                f.base_op_qtd =
                    CASE
                        WHEN d.base_calculo = 'M3_EXISTE_PROCESSO'
                            THEN COALESCE(f.base_m3, 0)

                        WHEN d.base_calculo = 'QTD_ESTRUTURA_X_M3'
                            THEN COALESCE(f.qtde_calc, 0) * COALESCE(f.base_m3, 0)

                        WHEN d.base_calculo = 'QTD_ESTRUTURA_X_M2'
                            THEN COALESCE(f.qtde_calc, 0) * COALESCE(f.base_m2, 0)

                        WHEN d.base_calculo = 'QTD_PROCESSO_X_COMPONENTE'
                            THEN COALESCE(f.qtde_calc, 0) * COALESCE(aux.qtde_calc, 0)

                        WHEN UPPER(COALESCE(c.contahora, '')) = 'M2'
                            THEN COALESCE(f.qtde_calc, 0) * COALESCE(f.base_m2, 0)

                        WHEN UPPER(COALESCE(c.contahora, '')) = 'M3'
                            THEN COALESCE(f.qtde_calc, 0) * COALESCE(f.base_m3, 0)

                        WHEN UPPER(COALESCE(c.contahora, '')) = 'KG'
                            THEN COALESCE(f.qtde_calc, 0) * COALESCE(f.base_kg, 0)

                        ELSE COALESCE(f.qtde_calc, 0)
                    END,

                f.base_op_origem =
                    CASE
                        WHEN d.base_calculo IS NOT NULL
                            THEN d.base_calculo

                        WHEN UPPER(COALESCE(c.contahora, '')) = 'M2'
                            THEN 'QTD_X_M2'

                        WHEN UPPER(COALESCE(c.contahora, '')) = 'M3'
                            THEN 'QTD_X_M3'

                        WHEN UPPER(COALESCE(c.contahora, '')) = 'KG'
                            THEN 'QTD_X_KG'

                        ELSE 'QTDE_CALC'
                    END,

                f.valor_op =
                    (
                        CASE
                            WHEN d.base_calculo = 'M3_EXISTE_PROCESSO'
                                THEN COALESCE(f.base_m3, 0)

                            WHEN d.base_calculo = 'QTD_ESTRUTURA_X_M3'
                                THEN COALESCE(f.qtde_calc, 0) * COALESCE(f.base_m3, 0)

                            WHEN d.base_calculo = 'QTD_ESTRUTURA_X_M2'
                                THEN COALESCE(f.qtde_calc, 0) * COALESCE(f.base_m2, 0)

                            WHEN d.base_calculo = 'QTD_PROCESSO_X_COMPONENTE'
                                THEN COALESCE(f.qtde_calc, 0) * COALESCE(aux.qtde_calc, 0)

                            WHEN UPPER(COALESCE(c.contahora, '')) = 'M2'
                                THEN COALESCE(f.qtde_calc, 0) * COALESCE(f.base_m2, 0)

                            WHEN UPPER(COALESCE(c.contahora, '')) = 'M3'
                                THEN COALESCE(f.qtde_calc, 0) * COALESCE(f.base_m3, 0)

                            WHEN UPPER(COALESCE(c.contahora, '')) = 'KG'
                                THEN COALESCE(f.qtde_calc, 0) * COALESCE(f.base_kg, 0)

                            ELSE COALESCE(f.qtde_calc, 0)
                        END
                    ) * c.custo_op_unit,

                f.updated_at = NOW()

            WHERE f.ano = ?
            AND f.mes = ?
            AND f.tipo_custo = 'OPERACIONAL'
        ";

        $itensAtualizados = DB::affectingStatement($sql, [
            $ano,
            $mes,
        ]);

        return [
            'ano' => $ano,
            'mes' => $mes,
            'ano_mes' => $anoMes,

            'itens_op_analisados' => $itensAnalisados,
            'itens_op_atualizados' => $itensAtualizados,
        ];
    }

    private function buscarDespesasPorSetor(int $anoBase, int $mesBase): array
    {
        $colunas = $this->colunasRealizadoMes($mesBase);

        $colValor = $colunas['valor'];
        $colTaxa = $colunas['taxa'];
        $colPrazo = $colunas['prazo'];

        $sql = "
            SELECT
                c.Cod AS cod_cc,
                c.Ca AS centro_custo,
                c.Fam AS cod_fam,

                COALESCE(d.despesa_direta, 0) AS despesa_direta,
                COALESCE(a.despesa_alocada, 0) AS despesa_alocada,

                COALESCE(d.despesa_direta, 0)
                + COALESCE(a.despesa_alocada, 0) AS despesas

            FROM ca c

            LEFT JOIN (
                SELECT
                    desp.CodCa AS cod_cc,
                    SUM(
                        IF(
                            desp.{$colValor} / POW(taxad.{$colTaxa} / 100 + 1, prazod.{$colPrazo} / 30) <> 0,
                            desp.{$colValor} / POW(taxad.{$colTaxa} / 100 + 1, prazod.{$colPrazo} / 30),
                            desp.{$colValor}
                        )
                    ) AS despesa_direta
                FROM desp

                LEFT JOIN ca
                    ON ca.Emp = desp.Emp
                    AND ca.Cod = desp.CodCa

                LEFT JOIN prazod
                    ON prazod.Emp = desp.Emp
                    AND prazod.CodConta = desp.CodConta
                    AND prazod.Ano = desp.Ano

                LEFT JOIN taxad
                    ON taxad.Emp = desp.Emp
                    AND taxad.Ano = desp.Ano

                WHERE desp.Emp = ?
                AND desp.Ano = ?
                AND desp.{$colValor} <> 0
                AND ca.Fam <> 0

                GROUP BY desp.CodCa
            ) d
                ON d.cod_cc = c.Cod

            LEFT JOIN (
                SELECT
                    despaloc.CodCa AS cod_cc,
                    SUM(despaloc.{$colValor}) AS despesa_alocada
                FROM despaloc

                LEFT JOIN ca
                    ON ca.Emp = despaloc.Emp
                    AND ca.Cod = despaloc.CodCa

                WHERE despaloc.Emp = ?
                AND despaloc.Ano = ?
                AND despaloc.{$colValor} <> 0
                AND ca.Fam <> 0

                GROUP BY despaloc.CodCa
            ) a
                ON a.cod_cc = c.Cod

            WHERE c.Emp = ?
            AND c.Fam <> 0
        ";

        $dados = DB::connection(self::MLC_CONNECTION)->select($sql, [
            self::EMPRESA_MLC,
            (string) $anoBase,
            self::EMPRESA_MLC,
            (string) $anoBase,
            self::EMPRESA_MLC,
        ]);

        return collect($dados)
            ->keyBy(function ($item) {
                return trim((string) $item->cod_cc);
            })
            ->toArray();
    }

    private function buscarBaseOperacionalPorSetor(int $anoBase, int $mesBase): array
    {
        $coluna = $this->colunaRealizadoValor($mesBase);
        $nomeMes = $this->nomeMes($mesBase);

        $sql = "
            SELECT
                hp.Ano AS ano,
                ? AS mes_entrada,
                ? AS mes,
                hp.Emp,
                'MLC-horaprod' AS estrutura,

                ca.Fam AS cod_fam,
                hp.CodCa AS cod_cc,
                ca.Ca AS centro_custo,

                SUM(hp.{$coluna}) AS base_qtd

            FROM horaprod hp

            LEFT JOIN ca
                ON ca.Emp = hp.Emp
                AND ca.Cod = hp.CodCa

            WHERE hp.Emp = ?
            AND hp.Ano = ?
            AND hp.{$coluna} <> 0
            AND ca.Fam <> 0

            GROUP BY
                hp.Ano,
                hp.Emp,
                ca.Fam,
                hp.CodCa,
                ca.Ca
        ";

        $dados = DB::connection(self::MLC_CONNECTION)->select($sql, [
            $mesBase,
            $nomeMes,
            self::EMPRESA_MLC,
            (string) $anoBase,
        ]);

        return collect($dados)
            ->keyBy(function ($item) {
                return trim((string) $item->cod_cc);
            })
            ->toArray();
    }

    private function periodoBaseOperacional(int $ano, int $mes): array
    {
        for ($mesTeste = $mes; $mesTeste >= 1; $mesTeste--) {
            if ($this->existeBaseOperacionalNoMes($ano, $mesTeste)) {
                return [$ano, $mesTeste];
            }
        }

        $anoAnterior = $ano - 1;

        for ($mesTeste = 12; $mesTeste >= 1; $mesTeste--) {
            if ($this->existeBaseOperacionalNoMes($anoAnterior, $mesTeste)) {
                return [$anoAnterior, $mesTeste];
            }
        }

        return [$ano, $mes];
    }

    private function existeBaseOperacionalNoMes(int $ano, int $mes): bool
    {
        $coluna = $this->colunaRealizadoValor($mes);

        $sql = "
            SELECT
                COALESCE(SUM(hp.{$coluna}), 0) AS total
            FROM horaprod hp

            LEFT JOIN ca
                ON ca.Emp = hp.Emp
                AND ca.Cod = hp.CodCa

            WHERE hp.Emp = ?
            AND hp.Ano = ?
            AND hp.{$coluna} <> 0
            AND ca.Fam <> 0
        ";

        $dados = DB::connection(self::MLC_CONNECTION)->select($sql, [
            self::EMPRESA_MLC,
            (string) $ano,
        ]);

        $total = isset($dados[0]->total)
            ? (float) $dados[0]->total
            : 0;

        return $total > 0;
    }

    private function colunasRealizadoMes(int $mes): array
    {
        $mapa = [
            1 => [
                'valor' => 'RJan',
                'taxa' => 'rjant',
                'prazo' => 'rjanp',
            ],
            2 => [
                'valor' => 'RFev',
                'taxa' => 'rFevt',
                'prazo' => 'rFevp',
            ],
            3 => [
                'valor' => 'RMar',
                'taxa' => 'rMart',
                'prazo' => 'rMarp',
            ],
            4 => [
                'valor' => 'RAbr',
                'taxa' => 'rAbrt',
                'prazo' => 'rAbrp',
            ],
            5 => [
                'valor' => 'RMai',
                'taxa' => 'rMait',
                'prazo' => 'rMaip',
            ],
            6 => [
                'valor' => 'RJun',
                'taxa' => 'rJunt',
                'prazo' => 'rJunp',
            ],
            7 => [
                'valor' => 'RJul',
                'taxa' => 'rJult',
                'prazo' => 'rJulp',
            ],
            8 => [
                'valor' => 'RAgo',
                'taxa' => 'rAgot',
                'prazo' => 'rAgop',
            ],
            9 => [
                'valor' => 'RSet',
                'taxa' => 'rSett',
                'prazo' => 'rSetp',
            ],
            10 => [
                'valor' => 'ROut',
                'taxa' => 'rOutt',
                'prazo' => 'rOutp',
            ],
            11 => [
                'valor' => 'RNov',
                'taxa' => 'rNovt',
                'prazo' => 'rNovp',
            ],
            12 => [
                'valor' => 'RDez',
                'taxa' => 'rDezt',
                'prazo' => 'rDezp',
            ],
        ];

        return $mapa[$mes];
    }

    private function colunaRealizadoValor(int $mes): string
    {
        $mapa = [
            1 => 'RJan',
            2 => 'RFev',
            3 => 'RMar',
            4 => 'RAbr',
            5 => 'RMai',
            6 => 'RJun',
            7 => 'RJul',
            8 => 'RAgo',
            9 => 'RSet',
            10 => 'ROut',
            11 => 'RNov',
            12 => 'RDez',
        ];

        return $mapa[$mes];
    }

    private function nomeMes(int $mes): string
    {
        $mapa = [
            1 => 'Jan',
            2 => 'Fev',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'Mai',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Set',
            10 => 'Out',
            11 => 'Nov',
            12 => 'Dez',
        ];

        return $mapa[$mes];
    }
}
