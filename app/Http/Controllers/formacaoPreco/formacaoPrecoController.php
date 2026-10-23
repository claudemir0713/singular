<?php

namespace App\Http\Controllers\FormacaoPreco;

use App\Http\Controllers\Controller;
use App\Models\EmpresaParametro;
use Illuminate\Http\Request;
use App\Services\FormacaoPrecoService;
use App\Models\FormacaoPreco;
use App\Models\FormacaoPrecoItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FormacaoPrecoController extends Controller
{

    public function formacaoPreco()
    {
        $parametro = EmpresaParametro::firstOrCreate(
            ['empresa_id' => 1],
            [
                'margem' => 0,
                'taxa_financeira' => 0,
                'icms' => 0,
                'simples' => 0,
                'pis' => 0,
                'cofins' => 0,
                'ir_csll' => 0,
                'comissao' => 0,
                'difal' => 0,
                'frete' => 0,
                'despesa_fixa' => 0,
                'outros' => 0,
            ]
        );

        return view('formacaoPreco.formacaoPreco', compact('parametro'));
    }


    public function buscarCustoProduto(Request $request,FormacaoPrecoService $service)
    {
        $termo = trim((string) $request->get('codigo'));

        $dadosSemCusto = [
            'codigo' => $termo,
            'tipo_item_custo' => 'SEM_CUSTO',
            'origem_custo' => 'SEM_CUSTO',
            'ano_mes_custo' => null,
            'encontrado' => false,
            'custo_mp_unit' => 0,
            'custo_op_unit' => 0,
            'custo_total_unit' => 0,
        ];

        if ($termo === '') {
            return response()->json([
                'success' => false,
                'message' => 'Informe o código ou nome do produto.',
                'data' => $dadosSemCusto,
            ], 422);
        }

        try {
            if (!ctype_digit($termo)) {
                $produtos = $service->buscarProdutosPorNome($termo);

                if ($produtos->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Nenhum produto encontrado.',
                        'data' => $dadosSemCusto,
                    ], 404);
                }

                return response()->json([
                    'success' => true,
                    'selecionar_produto' => true,
                    'message' => 'Selecione o produto desejado.',
                    'produtos' => $produtos,
                ]);
            }

            $custo = $service->buscarCustoProduto($termo);

            return response()->json([
                'success' => true,
                'selecionar_produto' => false,
                'message' => 'Consulta realizada com sucesso.',
                'data' => $custo,
            ]);
        } catch (\Throwable $e) {
            Log::error(
                'Erro ao buscar custo do produto na formação de preço.',
                [
                    'termo' => $termo,
                    'erro' => $e->getMessage(),
                    'arquivo' => $e->getFile(),
                    'linha' => $e->getLine(),
                ]
            );

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar custo do produto.',
                'data' => $dadosSemCusto,
            ], 500);
        }

    }

    // public function salvarFormacaoPreco(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'cod_produto' => 'required|string|max:30',
    //         'descricao_produto' => 'nullable|string|max:255',

    //         'tipo_item_custo' => 'nullable|string|max:50',
    //         'origem_custo' => 'nullable|string|max:50',

    //         'ano_custo' => 'nullable|integer',
    //         'mes_custo' => 'nullable|integer',
    //         'ano_mes_custo' => 'nullable|string|max:7',

    //         'embalagem' => 'nullable|string|max:10',
    //         'unid_med' => 'nullable|string|max:20',
    //         'tipo_mp' => 'nullable|string|max:30',

    //         'forma_calculo' => 'nullable|integer|in:0,1',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Existem campos obrigatórios não informados.',
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     try {
    //         $dados = [
    //             'cod_produto' => $request->get('cod_produto'),
    //             'descricao_produto' => $request->get('descricao_produto'),

    //             'tipo_item_custo' => $request->get('tipo_item_custo'),
    //             'origem_custo' => $request->get('origem_custo'),

    //             'ano_custo' => $request->get('ano_custo'),
    //             'mes_custo' => $request->get('mes_custo'),
    //             'ano_mes_custo' => $request->get('ano_mes_custo'),

    //             'embalagem' => $request->get('embalagem'),
    //             'custo_percentual' => $this->normalizarDecimal($request->get('custo_percentual')),

    //             'unid_med' => $request->get('unid_med'),
    //             'tipo_mp' => $request->get('tipo_mp'),

    //             'custo_materiais' => $this->normalizarDecimal($request->get('custo_materiais')),
    //             'custo_moi' => $this->normalizarDecimal($request->get('custo_moi')),
    //             'custo_moe' => $this->normalizarDecimal($request->get('custo_moe')),
    //             'vl_frete' => $this->normalizarDecimal($request->get('vl_frete')),
    //             'custo_total' => $this->normalizarDecimal($request->get('custo_total')),

    //             'prazo' => (int) $request->get('prazo', 0),
    //             'forma_calculo' => (int) $request->get('forma_calculo', 0),

    //             'margem' => $this->normalizarDecimal($request->get('margem')),
    //             'preco_informado' => $this->normalizarDecimal($request->get('preco_informado')),
    //             'comissao' => $this->normalizarDecimal($request->get('comissao')),

    //             'taxa_financeira' => $this->normalizarDecimal($request->get('taxa_financeira')),
    //             'icms' => $this->normalizarDecimal($request->get('icms')),
    //             'simples' => $this->normalizarDecimal($request->get('simples')),
    //             'pis' => $this->normalizarDecimal($request->get('pis')),
    //             'cofins' => $this->normalizarDecimal($request->get('cofins')),
    //             'ir_csll' => $this->normalizarDecimal($request->get('ir_csll')),
    //             'difal' => $this->normalizarDecimal($request->get('difal')),
    //             'frete_percentual' => $this->normalizarDecimal($request->get('frete_percentual')),
    //             'despesa_fixa' => $this->normalizarDecimal($request->get('despesa_fixa')),
    //             'outros' => $this->normalizarDecimal($request->get('outros')),

    //             'valor_venda' => $this->normalizarDecimal($request->get('valor_venda')),

    //             'valor_impostos' => $this->normalizarDecimal($request->get('valor_impostos')),
    //             'valor_outras_variaveis' => $this->normalizarDecimal($request->get('valor_outras_variaveis')),
    //             'valor_custo_financeiro' => $this->normalizarDecimal($request->get('valor_custo_financeiro')),

    //             'valor_margem_contribuicao' => $this->normalizarDecimal($request->get('valor_margem_contribuicao')),
    //             'percentual_margem_contribuicao' => $this->normalizarDecimal($request->get('percentual_margem_contribuicao')),

    //             'valor_resultado_bruto' => $this->normalizarDecimal($request->get('valor_resultado_bruto')),
    //             'percentual_resultado_bruto' => $this->normalizarDecimal($request->get('percentual_resultado_bruto')),

    //             'valor_lucro' => $this->normalizarDecimal($request->get('valor_lucro')),
    //             'percentual_lucro' => $this->normalizarDecimal($request->get('percentual_lucro')),
    //         ];

    //         $formacaoPreco = FormacaoPreco::create($dados);

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Formação de preço salva com sucesso.',
    //             'data' => $formacaoPreco,
    //         ]);
    //     } catch (\Throwable $e) {
    //         Log::error('Erro ao salvar formação de preço.', [
    //             'erro' => $e->getMessage(),
    //             'arquivo' => $e->getFile(),
    //             'linha' => $e->getLine(),
    //             'request' => $request->all(),
    //         ]);

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Erro ao salvar formação de preço.',
    //         ], 500);
    //     }
    // }

    public function salvarFormacaoPreco(Request $request)
    {
        $validator = Validator::make($request->all(), [
            /*
            * Cabeçalho.
            */
            'empresa_id' => 'nullable|integer',

            'dav_numero' => 'nullable|integer',
            'dav_numero_auxiliar' => 'nullable|string|max:50',
            'dav_tipo' => 'nullable|string|max:20',

            'dav_data' => 'nullable|date',
            'dav_data_emissao' => 'nullable|date',
            'dav_data_confirmacao' => 'nullable|date',
            'dav_data_competencia' => 'nullable|date',

            'tipo_pagamento' => 'nullable|string|max:30',
            'condicao_pagamento_codigo' => 'nullable|integer',
            'condicao_pagamento_descricao' => 'nullable|string|max:255',
            'numero_parcelas' => 'nullable|integer|min:0',
            'dias_primeiro_vencimento' => 'nullable|integer|min:0',
            'dias_proximos_vencimentos' => 'nullable|integer|min:0',

            'entidade_codigo' => 'nullable|integer',
            'adquirente_nome' => 'nullable|string|max:255',
            'adquirente_cpf_cnpj' => 'nullable|string|max:30',
            'adquirente_ie_rg' => 'nullable|string|max:30',
            'representante_codigo' => 'nullable|integer',

            'documento_numero' => 'nullable|integer',

            'situacao' => 'nullable|string|in:RASCUNHO,CALCULADA,APROVADA,CANCELADA',
            'observacao' => 'nullable|string',

            /*
            * Produto e item.
            */
            'item' => 'nullable|integer|min:1',
            'cod_produto' => 'required|string|max:30',
            'prd_codigo_digitado' => 'nullable|string|max:50',
            'descricao_produto' => 'nullable|string|max:255',
            'unid_med' => 'nullable|string|max:20',

            /*
            * Dados da origem do custo.
            */
            'tipo_item_custo' => 'nullable|string|max:50',
            'origem_custo' => 'nullable|string|max:50',

            'ano_custo' => 'nullable|integer',
            'mes_custo' => 'nullable|integer|min:1|max:12',
            'ano_mes_custo' => 'nullable|string|max:7',

            'embalagem' => 'nullable|string|max:30',
            'tipo_mp' => 'nullable|string|max:30',

            /*
            * Forma de cálculo.
            */
            'forma_calculo' => 'nullable|integer|in:0,1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Existem campos obrigatórios ou inválidos.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $resultado = DB::connection('mysql')->transaction(function () use ($request) {
                /*
                * Cabeçalho da Formação de Preço.
                */
                $dadosCabecalho = [
                    'empresa_id' => (int) $request->get('empresa_id', 1),

                    'dav_numero' => $request->filled('dav_numero')
                        ? (int) $request->get('dav_numero')
                        : null,

                    'dav_numero_auxiliar' => $request->filled('dav_numero_auxiliar')
                        ? trim($request->get('dav_numero_auxiliar'))
                        : null,

                    'dav_tipo' => $request->filled('dav_tipo')
                        ? trim($request->get('dav_tipo'))
                        : null,

                    'dav_data' => $request->filled('dav_data')
                        ? $request->get('dav_data')
                        : null,

                    'dav_data_emissao' => $request->filled('dav_data_emissao')
                        ? $request->get('dav_data_emissao')
                        : null,

                    'dav_data_confirmacao' => $request->filled('dav_data_confirmacao')
                        ? $request->get('dav_data_confirmacao')
                        : null,

                    'dav_data_competencia' => $request->filled('dav_data_competencia')
                        ? $request->get('dav_data_competencia')
                        : null,

                    'tipo_pagamento' => $request->filled('tipo_pagamento')
                        ? trim($request->get('tipo_pagamento'))
                        : null,

                    'condicao_pagamento_codigo' => $request->filled('condicao_pagamento_codigo')
                        ? (int) $request->get('condicao_pagamento_codigo')
                        : null,

                    'condicao_pagamento_descricao' => $request->filled('condicao_pagamento_descricao')
                        ? trim($request->get('condicao_pagamento_descricao'))
                        : null,

                    'numero_parcelas' => (int) $request->get('numero_parcelas', 1),

                    'dias_primeiro_vencimento' => (int) $request->get(
                        'dias_primeiro_vencimento',
                        0
                    ),

                    'dias_proximos_vencimentos' => (int) $request->get(
                        'dias_proximos_vencimentos',
                        0
                    ),

                    'prazo_medio_condicao' => $this->normalizarDecimal(
                        $request->get('prazo_medio_condicao')
                    ),

                    'entidade_codigo' => $request->filled('entidade_codigo')
                        ? (int) $request->get('entidade_codigo')
                        : null,

                    'adquirente_nome' => $request->filled('adquirente_nome')
                        ? trim($request->get('adquirente_nome'))
                        : null,

                    'adquirente_cpf_cnpj' => $request->filled('adquirente_cpf_cnpj')
                        ? trim($request->get('adquirente_cpf_cnpj'))
                        : null,

                    'adquirente_ie_rg' => $request->filled('adquirente_ie_rg')
                        ? trim($request->get('adquirente_ie_rg'))
                        : null,

                    'representante_codigo' => $request->filled('representante_codigo')
                        ? (int) $request->get('representante_codigo')
                        : null,

                    'documento_numero' => $request->filled('documento_numero')
                        ? (int) $request->get('documento_numero')
                        : null,

                    /*
                    * Valores originais do DAV.
                    */
                    'dav_valor' => $this->normalizarDecimal(
                        $request->get('dav_valor')
                    ),

                    'dav_valor_produtos' => $this->normalizarDecimal(
                        $request->get('dav_valor_produtos')
                    ),

                    'dav_valor_frete' => $this->normalizarDecimal(
                        $request->get('dav_valor_frete')
                    ),

                    'dav_percentual_desconto' => $this->normalizarDecimal(
                        $request->get('dav_percentual_desconto')
                    ),

                    'dav_valor_ipi' => $this->normalizarDecimal(
                        $request->get('dav_valor_ipi')
                    ),

                    'dav_valor_liquido' => $this->normalizarDecimal(
                        $request->get('dav_valor_liquido')
                    ),

                    /*
                    * Totais consolidados.
                    *
                    * Nesta primeira versão existe um item por formação,
                    * portanto os totais do cabeçalho recebem os valores
                    * calculados para o item.
                    */
                    'custo_total' => $this->normalizarDecimal(
                        $request->get('custo_total')
                    ),

                    'valor_venda' => $this->normalizarDecimal(
                        $request->get('valor_venda')
                    ),

                    'valor_margem_contribuicao' => $this->normalizarDecimal(
                        $request->get('valor_margem_contribuicao')
                    ),

                    'percentual_margem_contribuicao' => $this->normalizarDecimal(
                        $request->get('percentual_margem_contribuicao')
                    ),

                    'valor_resultado_bruto' => $this->normalizarDecimal(
                        $request->get('valor_resultado_bruto')
                    ),

                    'percentual_resultado_bruto' => $this->normalizarDecimal(
                        $request->get('percentual_resultado_bruto')
                    ),

                    'valor_lucro' => $this->normalizarDecimal(
                        $request->get('valor_lucro')
                    ),

                    'percentual_lucro' => $this->normalizarDecimal(
                        $request->get('percentual_lucro')
                    ),

                    'situacao' => $request->get('situacao', 'CALCULADA'),

                    'observacao' => $request->filled('observacao')
                        ? trim($request->get('observacao'))
                        : null,

                    'usuario_criacao_id' => Auth::id(),
                    'usuario_alteracao_id' => Auth::id(),
                ];

                $formacaoPreco = FormacaoPreco::create($dadosCabecalho);

                /*
                * Item da Formação de Preço.
                */
                $dadosItem = [
                    'formacao_preco_id' => $formacaoPreco->id,

                    'item' => (int) $request->get('item', 1),

                    /*
                    * Origem DAV Item.
                    */
                    'dav_numero' => $request->filled('dav_numero')
                        ? (int) $request->get('dav_numero')
                        : null,

                    'dav_tipo' => $request->filled('dav_tipo')
                        ? trim($request->get('dav_tipo'))
                        : null,

                    /*
                    * Produto.
                    */
                    'cod_produto' => trim($request->get('cod_produto')),

                    'prd_codigo_digitado' => $request->filled('prd_codigo_digitado')
                        ? trim($request->get('prd_codigo_digitado'))
                        : null,

                    'descricao_produto' => $request->filled('descricao_produto')
                        ? trim($request->get('descricao_produto'))
                        : null,

                    'unid_med' => $request->filled('unid_med')
                        ? trim($request->get('unid_med'))
                        : null,

                    'quantidade' => $this->normalizarDecimal(
                        $request->get('quantidade', 1)
                    ),

                    /*
                    * Valores originais do DAV Item.
                    */
                    'davi_vl_unit' => $this->normalizarDecimal(
                        $request->get('davi_vl_unit')
                    ),

                    'davi_vl_total_bruto' => $this->normalizarDecimal(
                        $request->get('davi_vl_total_bruto')
                    ),

                    'davi_vl_total' => $this->normalizarDecimal(
                        $request->get('davi_vl_total')
                    ),

                    'davi_totalizador' => $request->filled('davi_totalizador')
                        ? trim($request->get('davi_totalizador'))
                        : null,

                    'davi_cancelado' => $request->filled('davi_cancelado')
                        ? trim($request->get('davi_cancelado'))
                        : null,

                    'davi_aliquota' => $this->normalizarDecimal(
                        $request->get('davi_aliquota')
                    ),

                    'davi_qtde_entrega' => $this->normalizarDecimal(
                        $request->get('davi_qtde_entrega')
                    ),

                    'davi_qtde_volumes' => $this->normalizarDecimal(
                        $request->get('davi_qtde_volumes')
                    ),

                    'davi_casas_dec_valor_unitario' => (int) $request->get(
                        'davi_casas_dec_valor_unitario',
                        2
                    ),

                    'reg_codigo' => $request->filled('reg_codigo')
                        ? (int) $request->get('reg_codigo')
                        : null,

                    /*
                    * Tributos originais do DAV Item.
                    */
                    'davi_icms_cst' => $request->filled('davi_icms_cst')
                        ? trim($request->get('davi_icms_cst'))
                        : null,

                    'davi_icms_bc' => $this->normalizarDecimal(
                        $request->get('davi_icms_bc')
                    ),

                    'davi_icms_aliquota' => $this->normalizarDecimal(
                        $request->get('davi_icms_aliquota')
                    ),

                    'davi_icms_valor' => $this->normalizarDecimal(
                        $request->get('davi_icms_valor')
                    ),

                    'davi_pis_aliquota' => $this->normalizarDecimal(
                        $request->get('davi_pis_aliquota')
                    ),

                    'davi_pis_valor' => $this->normalizarDecimal(
                        $request->get('davi_pis_valor')
                    ),

                    'davi_cofins_aliquota' => $this->normalizarDecimal(
                        $request->get('davi_cofins_aliquota')
                    ),

                    'davi_cofins_valor' => $this->normalizarDecimal(
                        $request->get('davi_cofins_valor')
                    ),

                    'davi_qtde_produzir' => $this->normalizarDecimal(
                        $request->get('davi_qtde_produzir')
                    ),

                    'davi_comissao_origem' => $request->filled('davi_comissao_origem')
                        ? trim($request->get('davi_comissao_origem'))
                        : null,

                    'davi_comissao_perc' => $this->normalizarDecimal(
                        $request->get('davi_comissao_perc')
                    ),

                    'davi_icms_sn_bc' => $this->normalizarDecimal(
                        $request->get('davi_icms_sn_bc')
                    ),

                    'davi_vl_unit_negociacao' => $this->normalizarDecimal(
                        $request->get('davi_vl_unit_negociacao')
                    ),

                    'davi_vl_total_negociacao' => $this->normalizarDecimal(
                        $request->get('davi_vl_total_negociacao')
                    ),

                    'davi_volume_item_unit' => $this->normalizarDecimal(
                        $request->get('davi_volume_item_unit')
                    ),

                    'davi_volume_item_total' => $this->normalizarDecimal(
                        $request->get('davi_volume_item_total')
                    ),

                    /*
                    * Origem do custo.
                    */
                    'tipo_item_custo' => $request->filled('tipo_item_custo')
                        ? trim($request->get('tipo_item_custo'))
                        : null,

                    'origem_custo' => $request->filled('origem_custo')
                        ? trim($request->get('origem_custo'))
                        : null,

                    'ano_custo' => $request->filled('ano_custo')
                        ? (int) $request->get('ano_custo')
                        : null,

                    'mes_custo' => $request->filled('mes_custo')
                        ? (int) $request->get('mes_custo')
                        : null,

                    'ano_mes_custo' => $request->filled('ano_mes_custo')
                        ? trim($request->get('ano_mes_custo'))
                        : null,

                    'embalagem' => $request->filled('embalagem')
                        ? trim($request->get('embalagem'))
                        : null,

                    'custo_percentual' => $this->normalizarDecimal(
                        $request->get('custo_percentual')
                    ),

                    'tipo_mp' => $request->filled('tipo_mp')
                        ? trim($request->get('tipo_mp'))
                        : null,

                    /*
                    * Custos.
                    */
                    'custo_materiais' => $this->normalizarDecimal(
                        $request->get('custo_materiais')
                    ),

                    'custo_moi' => $this->normalizarDecimal(
                        $request->get('custo_moi')
                    ),

                    'custo_moe' => $this->normalizarDecimal(
                        $request->get('custo_moe')
                    ),

                    'vl_frete' => $this->normalizarDecimal(
                        $request->get('vl_frete')
                    ),

                    'custo_total' => $this->normalizarDecimal(
                        $request->get('custo_total')
                    ),

                    /*
                    * Entradas do cálculo.
                    */
                    'prazo' => $this->normalizarDecimal(
                        $request->get('prazo')
                    ),

                    'forma_calculo' => (int) $request->get(
                        'forma_calculo',
                        0
                    ),

                    'margem' => $this->normalizarDecimal(
                        $request->get('margem')
                    ),

                    'preco_informado' => $this->normalizarDecimal(
                        $request->get('preco_informado')
                    ),

                    /*
                    * Parâmetros fotografados.
                    */
                    'comissao' => $this->normalizarDecimal(
                        $request->get('comissao')
                    ),

                    'taxa_financeira' => $this->normalizarDecimal(
                        $request->get('taxa_financeira')
                    ),

                    'icms' => $this->normalizarDecimal(
                        $request->get('icms')
                    ),

                    'simples' => $this->normalizarDecimal(
                        $request->get('simples')
                    ),

                    'pis' => $this->normalizarDecimal(
                        $request->get('pis')
                    ),

                    'cofins' => $this->normalizarDecimal(
                        $request->get('cofins')
                    ),

                    'ir_csll' => $this->normalizarDecimal(
                        $request->get('ir_csll')
                    ),

                    'difal' => $this->normalizarDecimal(
                        $request->get('difal')
                    ),

                    'frete_percentual' => $this->normalizarDecimal(
                        $request->get('frete_percentual')
                    ),

                    'despesa_fixa' => $this->normalizarDecimal(
                        $request->get('despesa_fixa')
                    ),

                    'outros' => $this->normalizarDecimal(
                        $request->get('outros')
                    ),

                    /*
                    * Valores calculados.
                    */
                    'valor_venda' => $this->normalizarDecimal(
                        $request->get('valor_venda')
                    ),

                    'valor_impostos' => $this->normalizarDecimal(
                        $request->get('valor_impostos')
                    ),

                    'valor_outras_variaveis' => $this->normalizarDecimal(
                        $request->get('valor_outras_variaveis')
                    ),

                    'valor_custo_financeiro' => $this->normalizarDecimal(
                        $request->get('valor_custo_financeiro')
                    ),

                    /*
                    * Valores intermediários.
                    */
                    'valor_despesa_fixa' => $this->normalizarDecimal(
                        $request->get('valor_despesa_fixa')
                    ),

                    'valor_financeiro_despesa_fixa' => $this->normalizarDecimal(
                        $request->get('valor_financeiro_despesa_fixa')
                    ),

                    'valor_ir_csll' => $this->normalizarDecimal(
                        $request->get('valor_ir_csll')
                    ),

                    'valor_financeiro_ir_csll' => $this->normalizarDecimal(
                        $request->get('valor_financeiro_ir_csll')
                    ),

                    'taxa_financeira_efetiva' => $this->normalizarDecimal(
                        $request->get('taxa_financeira_efetiva')
                    ),

                    'equivalencia_financeira' => $this->normalizarDecimal(
                        $request->get('equivalencia_financeira')
                    ),

                    /*
                    * Resultados.
                    */
                    'valor_margem_contribuicao' => $this->normalizarDecimal(
                        $request->get('valor_margem_contribuicao')
                    ),

                    'percentual_margem_contribuicao' => $this->normalizarDecimal(
                        $request->get('percentual_margem_contribuicao')
                    ),

                    'valor_resultado_bruto' => $this->normalizarDecimal(
                        $request->get('valor_resultado_bruto')
                    ),

                    'percentual_resultado_bruto' => $this->normalizarDecimal(
                        $request->get('percentual_resultado_bruto')
                    ),

                    'valor_lucro' => $this->normalizarDecimal(
                        $request->get('valor_lucro')
                    ),

                    'percentual_lucro' => $this->normalizarDecimal(
                        $request->get('percentual_lucro')
                    ),
                ];

                $formacaoPrecoItem = FormacaoPrecoItem::create($dadosItem);

                return [
                    'formacao_preco' => $formacaoPreco,
                    'formacao_preco_item' => $formacaoPrecoItem,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Formação de preço salva com sucesso.',
                'data' => [
                    'id' => $resultado['formacao_preco']->id,
                    'formacao_preco' => $resultado['formacao_preco'],
                    'item' => $resultado['formacao_preco_item'],
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao salvar formação de preço.', [
                'erro' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'request' => $request->except([
                    '_token',
                ]),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao salvar formação de preço.',
            ], 500);
        }
    }

    private function normalizarDecimal($valor)
    {
        if ($valor === null || $valor === '') {
            return 0;
        }

        $valor = trim((string) $valor);

        if ($valor === '') {
            return 0;
        }

        if (strpos($valor, ',') !== false) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }

        return is_numeric($valor) ? $valor : 0;
    }

    public function gravarParametrosPadrao(Request $request)
    {
        try {
            $empresaId = 1;

            $parametro = EmpresaParametro::firstOrNew([
                'empresa_id' => $empresaId,
            ]);

            $parametro->empresa_id = $empresaId;

            $parametro->margem = $this->normalizarDecimal($request->get('md_margem'));
            $parametro->taxa_financeira = $this->normalizarDecimal($request->get('md_parametro_taxa_financeira'));
            $parametro->icms = $this->normalizarDecimal($request->get('md_parametro_icms'));
            $parametro->simples = $this->normalizarDecimal($request->get('md_parametro_simples'));
            $parametro->pis = $this->normalizarDecimal($request->get('md_parametro_pis'));
            $parametro->cofins = $this->normalizarDecimal($request->get('md_parametro_cofins'));
            $parametro->ir_csll = $this->normalizarDecimal($request->get('md_parametro_ir_csll'));
            $parametro->comissao = $this->normalizarDecimal($request->get('md_comissao'));
            $parametro->difal = $this->normalizarDecimal($request->get('md_parametro_difal'));
            $parametro->frete = $this->normalizarDecimal($request->get('md_parametro_frete_percentual'));
            $parametro->despesa_fixa = $this->normalizarDecimal($request->get('md_parametro_despesa_fixa'));
            $parametro->outros = $this->normalizarDecimal($request->get('md_parametro_outros'));

            $parametro->save();

            return response()->json([
                'success' => true,
                'message' => 'Parâmetros padrão gravados com sucesso.',
                'data' => $parametro,
            ]);
        } catch (\Throwable $e) {
            Log::error('Erro ao gravar parâmetros padrão da formação de preço.', [
                'erro' => $e->getMessage(),
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao gravar parâmetros padrão.',
            ], 500);
        }
    }


}
