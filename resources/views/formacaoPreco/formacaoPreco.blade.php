@extends('layouts.model')

@section('content')
    @php
        $version_js = env('VERSAO_JS');
        $version_css = env('VERSAO_CSS');

        $editando = isset($orcamento);

        $fmt = function ($valor) {
            return number_format($valor ?? 0, 2, ',', '.');
        };

        $parametro = $parametro ?? null;

        $taxaFinanceira = $parametro->taxa_financeira ?? 0;
        $icms = $parametro->icms ?? 0;
        $simples = $parametro->simples ?? 0;
        $pis = $parametro->pis ?? 0;
        $cofins = $parametro->cofins ?? 0;
        $irCsll = $parametro->ir_csll ?? 0;
        $difal = $parametro->difal ?? 0;
        $fretePercentual = $parametro->frete ?? 0;
        $despesaFixa = $parametro->despesa_fixa ?? 0;
        $outros = $parametro->outros ?? 0;
        $margem = $parametro->margem ?? 0;
        $comissao = $parametro->comissao ?? 0;

        $prazoDias = 30;
        $formaCalculo = 0;
    @endphp

    <div class="formacao-preco-page">

        <div class="row align-items-center mb-2">
            <div class="col-md-6">
                <div class="formacao-preco-titulo">
                    <i class="fas fa-hand-holding-usd"></i>
                    Nova Formação de Preço
                </div>

                <small class="text-muted fonte-10">
                    Informe o produto, confira os custos e simule o preço de venda.
                </small>
            </div>

            <div class="col-md-6 text-right">
                <div class="text-right produto-identificacao">
                    <div class="produto-codigo">
                        <i class="fas fa-calculator"></i>
                        Produto:
                        <strong id="label_produto_codigo">Não informado</strong>
                    </div>

                    <div id="label_produto_descricao" class="produto-descricao">
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <form id="form_formacao_preco" method="post">
            @csrf

            <input type="hidden" id="url_buscar_custo_produto" value="{{ route('formacaoPreco.buscarCustoProduto') }}">
            <input type="hidden" id="url_salvar_formacao_preco" value="{{ route('formacaoPreco.salvar') }}">


            <input type="hidden" name="empresa_parametro_id" id="empresa_parametro_id" value="{{ $parametro->id ?? 0 }}">
            <input type="hidden" name="empresa_id" id="empresa_id" value="{{ $parametro->empresa_id ?? 0 }}">
            <input type="hidden" name="orcamento_id" id="orcamento_id" value="">

            <input type="hidden" name="parametro_taxa_financeira" id="parametro_taxa_financeira"
                value="{{ $fmt($taxaFinanceira) }}">
            <input type="hidden" name="parametro_icms" id="parametro_icms" value="{{ $fmt($icms) }}">
            <input type="hidden" name="parametro_simples" id="parametro_simples" value="{{ $fmt($simples) }}">
            <input type="hidden" name="parametro_pis" id="parametro_pis" value="{{ $fmt($pis) }}">
            <input type="hidden" name="parametro_cofins" id="parametro_cofins" value="{{ $fmt($cofins) }}">
            <input type="hidden" name="parametro_ir_csll" id="parametro_ir_csll" value="{{ $fmt($irCsll) }}">
            <input type="hidden" name="parametro_difal" id="parametro_difal" value="{{ $fmt($difal) }}">
            <input type="hidden" name="parametro_frete_percentual" id="parametro_frete_percentual"
                value="{{ $fmt($fretePercentual) }}">
            <input type="hidden" name="parametro_despesa_fixa" id="parametro_despesa_fixa"
                value="{{ $fmt($despesaFixa) }}">
            <input type="hidden" name="parametro_outros" id="parametro_outros" value="{{ $fmt($outros) }}">

            <input type="hidden" name="preco_venda_calculado" id="preco_venda_calculado" value="">
            <input type="hidden" name="preco_venda" id="preco_venda" value="">
            <input type="hidden" name="forma_calculo" id="forma_calculo" value="{{ $formaCalculo }}">

            {{-- CONSULTA DO PRODUTO --}}
            <div class="row">
                <div class="form-group col-md-12">
                    <div class="card card-parametros">
                        <div class="card-header" align="center">
                            Consulta do Produto
                        </div>
                        <div class="card-body">
                            <div class="row align-items-end">

                                <div class="form-group col-md-2 fonte-10 campo-parametro-preco">
                                    <label class="label-parametro-preco">
                                        Código ou Nome do Produto
                                    </label>

                                    <input class="form-control fonte-10" type="text" id="codigo_produto"
                                        name="codigo_produto" placeholder="Código ou Nome do Produto" autocomplete="off">
                                </div>

                                <div class="form-group col-md-2 fonte-10 campo-parametro-preco">
                                    <label class="label-parametro-preco">
                                        Ação
                                    </label>

                                    <button type="button" class="btn btn-primary btn-sm btn-acao-formacao w-100"
                                        id="btn_buscar_produto">
                                        <span class="fas fa-search"></span>
                                        <span>Buscar</span>
                                    </button>
                                </div>

                                <div class="form-group col-md-8 fonte-10 campo-parametro-preco">
                                    <label class="label-parametro-preco">
                                        Status
                                    </label>

                                    <div id="msg_formacao_preco"></div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- PARÂMETROS --}}
            <div class="row">
                <div class="form-group col-md-12">
                    <div class="card card-parametros">
                        <div class="card-header" align="center">
                            Parâmetros
                        </div>

                        <div class="card-body">

                            <div class="row linha-parametros-comercial">

                                <div class="form-group col-md-2 fonte-10 campo-parametro-preco">
                                    <label class="label-parametro-preco">Prazo</label>

                                    <input class="form-control alinhaDireita fonte-10 atualiza_pv" type="number"
                                        step="any" id="prazo" name="prazo" value="{{ $prazoDias }}"
                                        inputmode="numeric" autocomplete="off">
                                </div>

                                <div class="form-group col-md-3 fonte-10 campo-parametro-preco">
                                    <label class="label-parametro-preco">Tipo de cálculo</label>

                                    <div class="tipo-calculo-wrapper">
                                        <input type="radio" class="btn-check atualiza_pv" name="formaCalculo1"
                                            id="formaCalculo1" checked>

                                        <label class="btn-tipo-calculo" for="formaCalculo1">
                                            <i class="fas fa-chart-line"></i>
                                            Margem
                                        </label>

                                        <input type="radio" class="btn-check atualiza_pv" name="formaCalculo1"
                                            id="formaCalculo2">

                                        <label class="btn-tipo-calculo" for="formaCalculo2">
                                            <i class="fas fa-tag"></i>
                                            Preço
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group col-md-3 fonte-10 campo-parametro-preco">
                                    <div class="grupo-margem-preco">

                                        <div class="campo-margem-preco-box">
                                            <label class="label-parametro-preco">Margem %</label>

                                            <input class="form-control alinhaDireita atualiza_pv fonte-10 mask-money"
                                                type="text" id="margem" name="margem"
                                                value="{{ $fmt($margem) }}" autocomplete="off">
                                        </div>

                                        <div class="campo-margem-preco-box campo-preco-box">
                                            <label class="label-parametro-preco">Preço R$</label>

                                            <div class="grupo-preco-desconto">
                                                <input
                                                    class="form-control alinhaDireita atualiza_pv fonte-10 mask-money campo-desabilitado"
                                                    type="text" id="margem1" name="margem1" value=""
                                                    placeholder="0,00" autocomplete="off" disabled>

                                                <button type="button"
                                                    class="btn btn-sm btn-outline-warning btn-desconto-preco"
                                                    id="btn_desconto_preco"
                                                    title="Simular desconto sobre o preço calculado">
                                                    <i class="fas fa-percent"></i>
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="form-group col-md-2 fonte-10 campo-parametro-preco">
                                    <label class="label-parametro-preco">Comissão %</label>

                                    <input class="form-control alinhaDireita atualiza_pv fonte-10 mask-money"
                                        type="text" id="comissao" name="comissao" value="{{ $fmt($comissao) }}"
                                        autocomplete="off">
                                </div>

                                <div class="form-group col-md-2 fonte-10 campo-parametro-preco">
                                    <label class="label-parametro-preco label-acoes">Ações</label>

                                    <div class="acoes-formacao-preco">
                                        <button type="button" class="btn btn-info btn-sm btn-acao-formacao"
                                            data-backdrop="static" data-keyboard="false" data-toggle="modal"
                                            data-target="#ModalParamentros">
                                            <span class="fas fa-donate"></span>
                                            <span>Parâmetros</span>
                                        </button>

                                        <button type="button" name="salvar" id="salvar_preco"
                                            class="btn btn-success btn-sm btn-acao-formacao">
                                            <span class="fas fa-save"></span>
                                            <span>Salvar</span>
                                        </button>

                                        <button type="button" id="btn_voltar"
                                            class="btn btn-warning btn-sm fonte-10 btn-acao-formacao w-100">
                                            <span class="fas fa-arrow-left"></span>
                                            <span>Voltar</span>
                                        </button>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- CARDS RESUMO --}}
            <div class="row">
                <div class="form-group col-md-12">
                    <div class="row mb-2">

                        <div class="col-md-4">
                            <div class="card card-resumo-preco card-resumo-venda">
                                <div class="card-body">
                                    <div class="resumo-titulo">
                                        <i class="fas fa-dollar-sign"></i>
                                        Valor Venda
                                    </div>

                                    <div class="resumo-valor" id="card_valor_venda">
                                        0,00
                                    </div>

                                    <div class="resumo-percentual">&nbsp;</div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card card-resumo-preco card-resumo-neutro" id="card_mc_box">
                                <div class="card-body">
                                    <div class="resumo-titulo">
                                        <i class="fas fa-chart-line"></i>
                                        MC - Margem de Contribuição
                                    </div>

                                    <div class="resumo-valor" id="card_mc_valor">
                                        0,00
                                    </div>

                                    <div class="resumo-percentual" id="card_mc_percentual">
                                        0,00%
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card card-resumo-preco card-resumo-neutro" id="card_ml_box">
                                <div class="card-body">
                                    <div class="resumo-titulo">
                                        <i class="fas fa-piggy-bank"></i>
                                        ML - Margem Líquida
                                    </div>

                                    <div class="resumo-valor" id="card_ml_valor">
                                        0,00
                                    </div>

                                    <div class="resumo-percentual" id="card_ml_percentual">
                                        0,00%
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- BOTÕES DE DETALHES --}}
            <div class="row">
                <div class="form-group col-md-12 text-right">
                    <button class="btn btn-secondary btn-sm fonte-10" type="button" data-toggle="collapse"
                        data-target="#collapseDadosCustosProduto" aria-expanded="false"
                        aria-controls="collapseDadosCustosProduto" id="btn_toggle_dados_custos">

                        <i class="fas fa-box-open"></i>
                        Ver dados e custos do produto
                    </button>

                    <button type="button" class="btn btn-info btn-sm fonte-10" id="btn_ver_estrutura_produto">
                        <i class="fas fa-sitemap"></i>
                        Ver estrutura do produto
                    </button>
                    <button type="button" class="btn btn-primary btn-sm fonte-10" id="btn_ver_memoria_calculo">
                        <i class="fas fa-list-alt"></i>
                        Ver memória de cálculo
                    </button>
                </div>
            </div>

            <div class="collapse" id="collapseDadosCustosProduto">

                {{-- DADOS DO PRODUTO --}}
                <div class="row">
                    <div class="form-group col-md-12">
                        <div class="card card-parametros">
                            <div class="card-header" align="center">
                                Dados do Produto
                            </div>

                            <div class="card-body">

                                <div class="row">
                                    <div class="form-group col-md-2 fonte-10">
                                        <label class="label-parametro-preco">Código</label>
                                        <input type="text" class="form-control fonte-10" id="codigo_resultado"
                                            readonly>
                                    </div>

                                    <div class="form-group col-md-4 fonte-10">
                                        <label class="label-parametro-preco">Descrição</label>
                                        <input type="text" class="form-control fonte-10" id="descricao_produto"
                                            readonly>
                                    </div>

                                    <div class="form-group col-md-2 fonte-10">
                                        <label class="label-parametro-preco">Tipo</label>
                                        <input type="text" class="form-control fonte-10" id="tipo_item_custo"
                                            readonly>
                                    </div>

                                    <div class="form-group col-md-2 fonte-10">
                                        <label class="label-parametro-preco">Origem</label>
                                        <input type="text" class="form-control fonte-10" id="origem_custo" readonly>
                                    </div>

                                    <div class="form-group col-md-2 fonte-10">
                                        <label class="label-parametro-preco">Ano/Mês</label>
                                        <input type="text" class="form-control fonte-10" id="ano_mes_custo" readonly>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="form-group col-md-2 fonte-10">
                                        <label class="label-parametro-preco">Ano</label>
                                        <input type="text" class="form-control fonte-10" id="ano_custo" readonly>
                                    </div>

                                    <div class="form-group col-md-2 fonte-10">
                                        <label class="label-parametro-preco">Mês</label>
                                        <input type="text" class="form-control fonte-10" id="mes_custo" readonly>
                                    </div>

                                    <div class="form-group col-md-2 fonte-10">
                                        <label class="label-parametro-preco">Embalagem</label>
                                        <input type="text" class="form-control fonte-10" id="embalagem" readonly>
                                    </div>

                                    <div class="form-group col-md-2 fonte-10">
                                        <label class="label-parametro-preco">% Custo</label>
                                        <input type="text" class="form-control fonte-10 alinhaDireita"
                                            id="custo_percentual" readonly>
                                    </div>

                                    <div class="form-group col-md-2 fonte-10">
                                        <label class="label-parametro-preco">Unid. Med.</label>
                                        <input type="text" class="form-control fonte-10" id="unid_med" readonly>
                                    </div>

                                    <div class="form-group col-md-2 fonte-10">
                                        <label class="label-parametro-preco">Tipo MP</label>
                                        <input type="text" class="form-control fonte-10" id="tipo_mp" readonly>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- CUSTOS DO PRODUTO --}}
                <div class="row">
                    <div class="form-group col-md-12">
                        <div class="card card-parametros">
                            <div class="card-header" align="center">
                                Custos do Produto
                            </div>

                            <div class="card-body">
                                <div class="row">

                                    <div class="form-group col-md-2 fonte-10 campo-parametro-preco">
                                        <label class="label-parametro-preco">Custo Materiais</label>
                                        <input class="form-control alinhaDireita atualiza_pv fonte-10 mask-money"
                                            type="text" id="custo_materiais" name="custo_materiais" value=""
                                            placeholder="0,00" autocomplete="off">
                                    </div>

                                    <div class="form-group col-md-2 fonte-10 campo-parametro-preco">
                                        <label class="label-parametro-preco">Custo MOI</label>
                                        <input class="form-control alinhaDireita atualiza_pv fonte-10 mask-money"
                                            type="text" id="custo_moi" name="custo_moi" value=""
                                            placeholder="0,00" autocomplete="off">
                                    </div>

                                    <div class="form-group col-md-2 fonte-10 campo-parametro-preco">
                                        <label class="label-parametro-preco">Custo MOE</label>
                                        <input class="form-control alinhaDireita atualiza_pv fonte-10 mask-money"
                                            type="text" id="custo_moe" name="custo_moe" value=""
                                            placeholder="0,00" autocomplete="off">
                                    </div>

                                    <div class="form-group col-md-2 fonte-10 campo-parametro-preco">
                                        <label class="label-parametro-preco">Frete R$</label>
                                        <input class="form-control alinhaDireita atualiza_pv fonte-10 mask-money"
                                            type="text" id="vl_frete" name="vl_frete" value=""
                                            placeholder="0,00" autocomplete="off">
                                    </div>

                                    <div class="form-group col-md-2 fonte-10 campo-parametro-preco">
                                        <label class="label-parametro-preco">Custo Base</label>
                                        <div class="form-control campo-total-custo alinhaDireita"
                                            id="vlr_custo_material_operacional">
                                            0,00
                                        </div>
                                    </div>

                                    <div class="form-group col-md-2 fonte-10 campo-parametro-preco">
                                        <label class="label-parametro-preco">% Custo</label>
                                        <div class="form-control campo-total-custo alinhaDireita"
                                            id="prec_custo_material_operacional">
                                            0,00
                                        </div>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="form-group col-md-12 fonte-10">
                                        <span id="resumo_status">Aguardando consulta do produto.</span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </form>

        @include('formacaoPreco.modalParametros')
        @include('formacaoPreco.modalListaProduto')
        @include('formacaoPreco.modalDescontoPreco')
        @include('formacaoPreco.modalEstruturaProduto')
        @include('formacaoPreco.modalMemoriaCalculo')

    </div>

    <script src="{{ asset('js/projeto/formacaoPreco.js?v=' . $version_js) }}"></script>

    <style>
        .formacao-preco-page {
            font-size: 10px;
        }

        .formacao-preco-titulo {
            font-size: 16px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 6px;
        }

        .formacao-preco-orcamento {
            font-size: 14px;
            font-weight: 600;
            color: #4b5563;
        }

        .alinhaDireita {
            text-align: right;
        }

        .card-parametros {
            margin-bottom: 12px;
        }

        .card-parametros .card-header {
            padding: 6px 10px !important;
            font-size: 12px !important;
        }

        .card-parametros .card-body {
            padding: 8px 14px 8px 14px !important;
        }

        .linha-parametros-comercial {
            align-items: stretch;
        }

        .campo-parametro-preco {
            padding-left: 6px !important;
            padding-right: 6px !important;
        }

        .label-parametro-preco {
            display: block;
            font-size: 10px !important;
            font-weight: 700;
            color: #374151;
            margin-bottom: 4px;
        }

        .label-acoes {
            text-align: center;
        }

        .tipo-calculo-wrapper {
            display: flex;
            gap: 6px;
            width: 100%;
        }

        .btn-check {
            display: none;
        }

        .btn-tipo-calculo {
            flex: 1;
            height: 34px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            background: #ffffff;
            color: #495057;
            font-size: 10px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            cursor: pointer;
            margin: 0;
            transition: all 0.15s ease-in-out;
        }

        .btn-tipo-calculo:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }

        #formaCalculo1:checked+label {
            background: #e8f5ff;
            border-color: #0d6efd;
            color: #0d6efd;
            box-shadow: inset 0 0 0 1px #0d6efd;
        }

        #formaCalculo2:checked+label {
            background: #fff8e1;
            border-color: #ffc107;
            color: #946200;
            box-shadow: inset 0 0 0 1px #ffc107;
        }

        .grupo-margem-preco {
            display: grid;
            grid-template-columns: 1fr 1.25fr;
            gap: 6px;
        }

        .campo-margem-preco-box {
            display: flex;
            flex-direction: column;
        }

        .grupo-preco-desconto {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .grupo-preco-desconto #margem1 {
            flex: 1;
        }

        .btn-desconto-preco {
            height: 28px !important;
            min-height: 28px !important;
            width: 30px !important;
            padding: 0 !important;
            font-size: 10px !important;
            line-height: 26px !important;
        }

        .campo-desabilitado {
            background-color: #e9ecef !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
        }

        .acoes-formacao-preco {
            display: grid;
            grid-template-columns: 1fr;
            gap: 5px;
        }

        .btn-acao-formacao {
            height: 30px !important;
            min-height: 30px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 5px;
            line-height: 1 !important;
            padding: 0 8px !important;
            font-size: 10px !important;
            white-space: nowrap;
        }

        .card-resumo-preco {
            border-radius: 6px;
            border: 1px solid #d9dee3;
            min-height: 82px;
            transition: all 0.2s ease-in-out;
        }

        .card-resumo-preco .card-body {
            padding: 10px 14px !important;
        }

        .resumo-titulo {
            font-size: 11px;
            font-weight: 600;
            color: #4b5563;
            margin-bottom: 5px;
        }

        .resumo-valor {
            font-size: 20px;
            font-weight: 700;
            line-height: 24px;
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .resumo-percentual {
            font-size: 11px;
            font-weight: 600;
            text-align: right;
            margin-top: 2px;
            font-variant-numeric: tabular-nums;
        }

        .card-resumo-venda {
            background: #eef6ff;
            border-left: 5px solid #0d6efd;
        }

        .card-resumo-venda .resumo-valor {
            color: #0d6efd;
        }

        .card-resumo-positivo {
            background: #eefaf1;
            border-left: 5px solid #28a745;
        }

        .card-resumo-positivo .resumo-valor,
        .card-resumo-positivo .resumo-percentual {
            color: #198754;
        }

        .card-resumo-negativo {
            background: #fff1f2;
            border-left: 5px solid #dc3545;
        }

        .card-resumo-negativo .resumo-valor,
        .card-resumo-negativo .resumo-percentual {
            color: #dc3545;
        }

        .card-resumo-neutro {
            background: #f8f9fa;
            border-left: 5px solid #adb5bd;
        }

        .card-resumo-neutro .resumo-valor,
        .card-resumo-neutro .resumo-percentual {
            color: #6c757d;
        }

        .campo-total-custo {
            height: 31px;
            background: #e9ecef;
            font-weight: 700;
            font-size: 10px;
            line-height: 18px;
        }

        #msg_formacao_preco .alert {
            padding: 6px 10px;
            margin-bottom: 0;
            font-size: 10px;
        }

        @media (max-width: 768px) {
            .linha-parametros-comercial {
                gap: 8px;
            }

            .campo-parametro-preco {
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .tipo-calculo-wrapper {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .btn-tipo-calculo {
                height: 38px;
                font-size: 11px;
            }

            .grupo-margem-preco {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .acoes-formacao-preco {
                grid-template-columns: 1fr;
                gap: 6px;
            }

            .btn-acao-formacao {
                height: 34px !important;
                font-size: 11px !important;
            }
        }

        /* Destaque do produto no desktop */
            .formacao-preco-page .produto-identificacao {
                width: 100% !important;
                max-width: 720px !important;
                margin-left: auto !important;
                padding: 8px 12px !important;
                text-align: right !important;
                background-color: transparent !important;
                border-right: 4px solid #0d6efd !important;
                border-radius: 4px !important;
            }

            .formacao-preco-page .produto-codigo {
                color: #64748b !important;
                font-size: 12px !important;
                font-weight: 500 !important;
                margin-bottom: 4px !important;
            }

            .formacao-preco-page .produto-codigo strong {
                color: #334155 !important;
                font-size: 13px !important;
                font-weight: 700 !important;
            }

            .formacao-preco-page .produto-descricao {
                display: block;
                color: #1e293b !important;
                font-size: 17px !important;
                font-weight: 700 !important;
                line-height: 1.3 !important;
                letter-spacing: 0.2px !important;
                text-transform: uppercase !important;
            }

            .formacao-preco-page .produto-descricao:empty {
                display: none;
            }
    </style>
@endsection
