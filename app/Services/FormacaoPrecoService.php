<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class FormacaoPrecoService
{
    public function buscarCustoProduto(string $codigo): array
    {
        $codigo = $this->normalizarCodigo($codigo);

        if ($codigo == '') {
            return $this->retornoSemCusto($codigo, 'Código do produto não informado.');
        }

        $estrutura = $this->buscarUltimoCustoEstrutura($codigo);



        if ($estrutura) {
            $itensEstrutura = $this->buscarEstruturaProduto(
                $codigo,
                (int) $estrutura->ano,
                (int) $estrutura->mes
            );

            return [
                'codigo' => trim((string) $estrutura->cod),
                'descricao' => trim((string) $estrutura->descr),
                'ano' => (int) $estrutura->ano,
                'mes' => (int) $estrutura->mes,
                'ano_mes_custo' => trim((string) $estrutura->ano_mes),

                'tipo_item_custo' => 'PRODUTO_ACABADO',
                'origem_custo' => 'ESTRUTURA',

                'embalagem' => $estrutura->embalagem,
                'custo_percentual' => (float) $estrutura->custo_percentual,

                'custo_mp_unit' => (float) $estrutura->custo_mp,
                'custo_op_unit' => (float) $estrutura->custo_op,
                'custo_total_unit' => (float) $estrutura->custo_total,

                'estrutura' => $itensEstrutura,

                'encontrado' => true,
                'message' => 'Custo encontrado na estrutura.',
            ];
        }

        $mp = $this->buscarUltimoCustoMp($codigo);

        if ($mp) {
            return [
                'codigo' => trim((string) $mp->cod_mp),
                'descricao' => trim((string) $mp->desc_mp),
                'ano' => (int) $mp->ano,
                'mes' => (int) $mp->mes,
                'ano_mes_custo' => trim((string) $mp->ano_mes),

                'tipo_item_custo' => 'MATERIA_PRIMA',
                'origem_custo' => 'MP',

                'embalagem' => null,
                'custo_percentual' => 1,

                'unid_med' => $mp->unid_med,
                'tipo_mp' => $mp->tipo,

                'custo_mp_unit' => (float) $mp->custo_ok,
                'custo_op_unit' => 0,
                'custo_total_unit' => (float) $mp->custo_ok,

                'encontrado' => true,
                'message' => 'Custo encontrado como matéria-prima.',
            ];
        }

        return $this->retornoSemCusto($codigo, 'Custo não encontrado para o item informado.');
    }

    private function buscarUltimoCustoEstrutura(string $codigo)
    {
        return DB::table('produto_custo_estrutura_mysql')
            ->where('cod', $codigo)
            ->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->first();
    }

    private function buscarUltimoCustoMp(string $codigo)
    {
        return DB::table('produto_custos_mp_mysql')
            ->where('cod_mp', $codigo)
            ->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->first();
    }

    private function retornoSemCusto(string $codigo, string $mensagem): array
    {
        return [
            'codigo' => $codigo,
            'descricao' => '',
            'ano' => null,
            'mes' => null,
            'ano_mes_custo' => null,

            'tipo_item_custo' => 'SEM_CUSTO',
            'origem_custo' => 'SEM_CUSTO',

            'embalagem' => null,
            'custo_percentual' => 1,

            'unid_med' => null,
            'tipo_mp' => null,

            'custo_mp_unit' => 0,
            'custo_op_unit' => 0,
            'custo_total_unit' => 0,

            'encontrado' => false,
            'message' => $mensagem,
        ];
    }

    public function buscarProdutosPorNome(string $termo)
    {
        $termo = trim($termo);

        if ($termo === '') {
            return collect();
        }

        return DB::table('produto_custo_estrutura_mysql')
            ->select('cod', 'descr')
            ->where('descr', 'like', '%' . $termo . '%')
            ->groupBy('cod', 'descr')
            ->orderBy('descr')
            ->get();
    }

    private function normalizarCodigo(string $codigo): string
    {
        return trim((string) $codigo);
    }


    private function buscarEstruturaProduto(string $codigo, int $ano, int $mes): array
    {
        $itens = DB::table('produto_ficha_custo_mysql')
            ->where('cod', $codigo)
            ->where('ano', $ano)
            ->where('mes', $mes)
            ->select([
                'cod_pai',
                'cod_comp',
                'desc_comp',
                'qtde_calc',
                'nivel',
                'tipo_custo',
                'tipo_mp',
                'custo_var',
                'base_op_qtd',
                'custo_mp_unit',
                'valor_mp',
                'custo_op_unit',
                'valor_op',
            ])
            ->orderBy('nivel')
            ->orderBy('tipo_custo')
            ->orderBy('desc_comp')
            ->get();

        $materiasPrimas = $itens
            ->filter(function ($item) {
                return trim((string) $item->tipo_custo) === 'MATÉRIA-PRIMA';
            })
            ->values();

        $operacionais = $itens
            ->filter(function ($item) {
                return trim((string) $item->tipo_custo) === 'OPERACIONAL';
            })
            ->values();

        return [
            'encontrado' => $itens->isNotEmpty(),
            'ano' => $ano,
            'mes' => $mes,
            'ano_mes' => sprintf('%04d-%02d', $ano, $mes),

            'materias_primas' => $materiasPrimas,
            'operacionais' => $operacionais,

            'total_materia_prima' => (float) $materiasPrimas->sum('valor_mp'),
            'total_operacional' => (float) $operacionais->sum('valor_op'),
        ];
    }
}
