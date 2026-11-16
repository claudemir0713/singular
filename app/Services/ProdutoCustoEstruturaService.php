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

        $linhas = DB::table('produto_ficha_custo_mysql')
            ->where('ano', $ano)
            ->where('mes', $mes)
            ->select([
                'cod', 'descr', 'grupo', 'cod_pai', 'cod_comp', 'tipo_custo',
                'custo_mp_unit', 'valor_mp', 'valor_op', 'base_m2', 'base_m3',
            ])
            ->orderBy('id')
            ->get()
            ->groupBy('cod');

        $embalagens = DB::table('produto_embalagem_mysql')
            ->select('cod_prod', 'embalagem', 'custo_percentual')
            ->get()
            ->keyBy(function ($e) {
                return trim((string) $e->cod_prod);
            });

        $unidades = $this->buscarUnidadesErp($linhas->keys()->all());

        $registros = [];
        $agora = now();

        foreach ($linhas as $cod => $itens) {
            $itens = $itens->all();

            // Não soma o que está abaixo de um componente que já tem custo próprio.
            $considerados = config('ficha.regra_nova')
                ? FichaCustoHierarquia::considerados($itens)
                : array_fill_keys(array_keys($itens), true);

            $custoMp = 0;
            $custoOp = 0;
            $baseM2 = 0;
            $baseM3 = 0;

            foreach ($itens as $i => $item) {
                $baseM2 = max($baseM2, (float) $item->base_m2);
                $baseM3 = max($baseM3, (float) $item->base_m3);

                if (!$considerados[$i]) {
                    continue;
                }

                $tipo = trim((string) $item->tipo_custo);

                if ($tipo === 'MATÉRIA-PRIMA') {
                    $custoMp += (float) $item->valor_mp;
                } elseif ($tipo === 'OPERACIONAL') {
                    $custoOp += (float) $item->valor_op;
                }
            }

            $unidade = $unidades[trim((string) $cod)] ?? null;
            $emb = $embalagens[trim((string) $cod)] ?? null;

            $registros[] = [
                'ano' => $ano,
                'mes' => $mes,
                'ano_mes' => $anoMes,

                'cod' => $cod,
                'descr' => $itens[0]->descr,
                'grupo' => $itens[0]->grupo,

                'embalagem' => $emb->embalagem ?? 'A',
                'custo_percentual' => $emb->custo_percentual ?? 1,

                'unid_med' => $unidade,
                'base_m2' => $baseM2,
                'base_m3' => $baseM3,
                'fator_unid' => FichaCustoHierarquia::fatorUnidade($unidade, $baseM2, $baseM3),

                'custo_mp' => $custoMp,
                'custo_op' => $custoOp,
                'custo_total' => $custoMp + $custoOp,

                'origem' => 'CALCULADO',
                'created_at' => $agora,
                'updated_at' => $agora,
            ];
        }

        foreach (array_chunk($registros, 500) as $lote) {
            produto_custo_estrutura::insert($lote);
        }

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

    /**
     * Unidade de venda (UM_CODIGO) de cada produto no ERP.
     */
    private function buscarUnidadesErp(array $codigos): array
    {
        $unidades = [];

        foreach (array_chunk($codigos, 500) as $lote) {
            $linhas = DB::connection(env('ERP_CONNECTION'))
                ->table('PRODUTOS')
                ->whereIn('PRD_CODIGO', $lote)
                ->select('PRD_CODIGO', 'UM_CODIGO')
                ->get();

            foreach ($linhas as $linha) {
                $unidades[trim((string) $linha->PRD_CODIGO)] = trim((string) $linha->UM_CODIGO);
            }
        }

        return $unidades;
    }
}
