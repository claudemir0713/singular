<?php

namespace App\Services;

use App\Models\produto_custo_estrutura;
use Illuminate\Support\Facades\DB;

class ProdutoCustoEstruturaService
{
    public function consolidar(int $ano, int $mes): array
    {
        $anoMes = $ano . '-' . str_pad($mes, 2, '0', STR_PAD_LEFT);

        produto_custo_estrutura::where('ano', $ano)
            ->where('mes', $mes)
            ->delete();

        $sqlInsert = "
            INSERT INTO produto_custo_estrutura_mysql
            (
                ano,
                mes,
                ano_mes,
                cod,
                descr,
                grupo,
                embalagem,
                custo_percentual,
                custo_mp,
                custo_op,
                custo_total,
                origem,
                created_at,
                updated_at
            )
            SELECT
                f.ano,
                f.mes,
                ? AS ano_mes,

                f.cod,
                MAX(f.descr) AS descr,
                MAX(f.grupo) AS grupo,

                COALESCE(MAX(e.embalagem), 'A') AS embalagem,
                COALESCE(MAX(e.custo_percentual), 1) AS custo_percentual,

                SUM(
                    CASE
                        WHEN f.tipo_custo = 'MATÉRIA-PRIMA'
                        THEN COALESCE(f.valor_mp, 0)
                        ELSE 0
                    END
                ) AS custo_mp,

                SUM(
                    CASE
                        WHEN f.tipo_custo = 'OPERACIONAL'
                        THEN COALESCE(f.valor_op, 0)
                        ELSE 0
                    END
                ) AS custo_op,

                SUM(
                    CASE
                        WHEN f.tipo_custo = 'MATÉRIA-PRIMA'
                        THEN COALESCE(f.valor_mp, 0)
                        ELSE 0
                    END
                )
                +
                SUM(
                    CASE
                        WHEN f.tipo_custo = 'OPERACIONAL'
                        THEN COALESCE(f.valor_op, 0)
                        ELSE 0
                    END
                ) AS custo_total,

                'CALCULADO' AS origem,
                NOW() AS created_at,
                NOW() AS updated_at

            FROM produto_ficha_custo_mysql f

            LEFT JOIN produto_embalagem_mysql e
                ON e.cod_prod = f.cod

            WHERE f.ano = ?
            AND f.mes = ?

            GROUP BY
                f.ano,
                f.mes,
                f.cod

            ORDER BY
                f.cod
        ";

        DB::insert($sqlInsert, [
            $anoMes,
            $ano,
            $mes,
        ]);

        $resumo = produto_custo_estrutura::where('ano', $ano)
            ->where('mes', $mes)
            ->selectRaw('
                COUNT(*) AS registros,
                COALESCE(SUM(custo_mp), 0) AS custo_mp_total,
                COALESCE(SUM(custo_op), 0) AS custo_op_total,
                COALESCE(SUM(custo_total), 0) AS custo_total
            ')
            ->first();

        return [
            'ano' => $ano,
            'mes' => $mes,
            'ano_mes' => $anoMes,

            'registros_consolidados' => (int) ($resumo->registros ?? 0),
            'custo_mp_total' => (float) ($resumo->custo_mp_total ?? 0),
            'custo_op_total' => (float) ($resumo->custo_op_total ?? 0),
            'custo_total' => (float) ($resumo->custo_total ?? 0),
        ];
    }
}
