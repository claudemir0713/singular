<div class="modal fade modalMemoriaCalculo" id="modalMemoriaCalculo" tabindex="-1" role="dialog" aria-labelledby="modalMemoriaCalculoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content fonte-10">

            <div class="modal-header">
                <h5 class="modal-title fonte-12 negrito" id="modalMemoriaCalculoLabel">
                    <i class="fas fa-calculator"></i> Memória de Cálculo do Preço de Venda
                    <span id="modal_memoria_calculo_titulo"></span>
                </h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                {{-- RESUMO PRINCIPAL --}}
                <div class="row">

                    <div class="col-md-3">
                        <div class="card border-info mb-3 memoria-card-resumo">
                            <div class="card-body text-center">
                                <small class="text-muted">Custo Materiais</small>
                                <h5 class="negrito mb-0" id="memoria_custo_materiais">R$ 0,00</h5>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-secondary mb-3 memoria-card-resumo">
                            <div class="card-body text-center">
                                <small class="text-muted">Custo Processos</small>
                                <h5 class="negrito mb-0" id="memoria_custo_processos">R$ 0,00</h5>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card border-warning mb-3 memoria-card-resumo">
                            <div class="card-body text-center">
                                <small class="text-muted">Custo Total</small>
                                <h5 class="negrito mb-0" id="memoria_custo_total">R$ 0,00</h5>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-success text-white mb-3 memoria-card-resumo">
                            <div class="card-body text-center">
                                <small>Preço de Venda</small>
                                <h5 class="negrito mb-0" id="memoria_preco_venda">R$ 0,00</h5>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- FÓRMULA APLICADA --}}
                <div class="card mb-3">
                    <div class="card-header fonte-12 negrito">
                        <i class="fas fa-square-root-alt"></i> Fórmula aplicada
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-3">
                                <small class="text-muted">Forma de Cálculo</small>
                                <h5 class="negrito" id="memoria_forma_calculo">Margem</h5>
                            </div>

                            <div class="col-md-3">
                                <small class="text-muted">Percentual de Venda</small>
                                <h5 class="negrito" id="memoria_percentual_venda">0,00%</h5>
                            </div>

                            <div class="col-md-3">
                                <small class="text-muted">Markup / Divisor</small>
                                <h5 class="negrito" id="memoria_divisor_venda">0,000000</h5>
                            </div>

                            <div class="col-md-3">
                                <small class="text-muted">Cálculo</small>
                                <div>
                                    <code id="memoria_calculo">0,00 / 0,000000</code>
                                </div>
                            </div>

                        </div>

                        <hr>

                        <div>
                            <small class="text-muted">Fórmula</small>
                            <div>
                                <code id="memoria_formula">preco_venda = custo_total / divisor</code>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="row">

                    {{-- IMPOSTOS SOBRE VENDA --}}
                    <div class="col-md-6">

                        <div class="card mb-3">
                            <div class="card-header fonte-12 negrito">
                                <i class="fas fa-percentage"></i> Impostos sobre Venda
                            </div>

                            <div class="card-body">

                                <div class="memoria-linha">
                                    <span>ICMS</span>
                                    <strong id="memoria_icms">0,00%</strong>
                                </div>
                                <div class="progress memoria-progress">
                                    <div id="memoria_icms_bar" class="progress-bar bg-info" style="width: 0%"></div>
                                </div>

                                <div class="memoria-linha">
                                    <span>Simples</span>
                                    <strong id="memoria_simples">0,00%</strong>
                                </div>
                                <div class="progress memoria-progress">
                                    <div id="memoria_simples_bar" class="progress-bar bg-info" style="width: 0%"></div>
                                </div>

                                <div class="memoria-linha">
                                    <span>PIS</span>
                                    <strong id="memoria_pis">0,00%</strong>
                                </div>
                                <div class="progress memoria-progress">
                                    <div id="memoria_pis_bar" class="progress-bar bg-info" style="width: 0%"></div>
                                </div>

                                <div class="memoria-linha">
                                    <span>COFINS</span>
                                    <strong id="memoria_cofins">0,00%</strong>
                                </div>
                                <div class="progress memoria-progress">
                                    <div id="memoria_cofins_bar" class="progress-bar bg-info" style="width: 0%"></div>
                                </div>

                                <hr>

                                <div class="memoria-linha memoria-total">
                                    <span>Imposto Venda Total</span>
                                    <strong id="memoria_imposto_venda">0,00%</strong>
                                </div>

                                <div class="memoria-linha memoria-total mt-2">
                                    <span>Valor dos Impostos</span>
                                    <strong id="memoria_valor_impostos">R$ 0,00</strong>
                                </div>

                            </div>
                        </div>

                        {{-- DESPESAS E ENCARGOS --}}
                        <div class="card mb-3">
                            <div class="card-header fonte-12 negrito">
                                <i class="fas fa-truck"></i> Despesas e Encargos
                            </div>

                            <div class="card-body">

                                <div class="memoria-linha">
                                    <span>Comissão</span>
                                    <strong id="memoria_comissao">0,00%</strong>
                                </div>
                                <div class="progress memoria-progress">
                                    <div id="memoria_comissao_bar" class="progress-bar bg-warning" style="width: 0%"></div>
                                </div>

                                <div class="memoria-linha">
                                    <span>Frete %</span>
                                    <strong id="memoria_frete_percentual">0,00%</strong>
                                </div>
                                <div class="progress memoria-progress">
                                    <div id="memoria_frete_percentual_bar" class="progress-bar bg-warning" style="width: 0%"></div>
                                </div>

                                <div class="memoria-linha">
                                    <span>Despesa Fixa</span>
                                    <strong id="memoria_despesa_fixa">0,00%</strong>
                                </div>
                                <div class="progress memoria-progress">
                                    <div id="memoria_despesa_fixa_bar" class="progress-bar bg-warning" style="width: 0%"></div>
                                </div>

                                <div class="memoria-linha">
                                    <span>DIFAL</span>
                                    <strong id="memoria_difal">0,00%</strong>
                                </div>

                                <div class="memoria-linha">
                                    <span>IR/CSLL</span>
                                    <strong id="memoria_ir_csll">0,00%</strong>
                                </div>

                                <hr>

                                <div class="memoria-linha memoria-total">
                                    <span>Outras Variáveis</span>
                                    <strong id="memoria_valor_outras_variaveis">R$ 0,00</strong>
                                </div>

                            </div>
                        </div>

                    </div>

                    {{-- OUTROS CUSTOS, MARGEM E FINANCEIRO --}}
                    <div class="col-md-6">

                        <div class="card mb-3">
                            <div class="card-header fonte-12 negrito">
                                <i class="fas fa-box-open"></i> Outros Custos e Margem
                            </div>

                            <div class="card-body">

                                <div class="memoria-linha">
                                    <span>Outros</span>
                                    <strong id="memoria_outros">0,00%</strong>
                                </div>

                                <div class="memoria-linha">
                                    <span>Material de Consumo</span>
                                    <strong id="memoria_material_consumo">0,00%</strong>
                                </div>

                                <hr>

                                <div class="memoria-linha memoria-total">
                                    <span>Outros + Material Consumo</span>
                                    <strong id="memoria_outros_com_material_consumo">0,00%</strong>
                                </div>

                                <hr>

                                <div class="memoria-linha">
                                    <span>Margem de Lucro</span>
                                    <strong id="memoria_margem_lucro">0,00%</strong>
                                </div>
                                <div class="progress memoria-progress">
                                    <div id="memoria_margem_lucro_bar" class="progress-bar bg-success" style="width: 0%"></div>
                                </div>

                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header fonte-12 negrito">
                                <i class="fas fa-calendar-alt"></i> Financeiro
                            </div>

                            <div class="card-body">

                                <div class="memoria-linha">
                                    <span>Prazo Médio</span>
                                    <strong id="memoria_prazo_medio">0 dias</strong>
                                </div>

                                <div class="memoria-linha">
                                    <span>Taxa Financeira</span>
                                    <strong id="memoria_taxa_financeira">0,0000%</strong>
                                </div>

                                <div class="memoria-linha">
                                    <span>Equivalência Financeira</span>
                                    <strong id="memoria_equivalencia">1,000000</strong>
                                </div>

                                <div class="memoria-linha memoria-total">
                                    <span>Markup Final</span>
                                    <strong id="memoria_markup">0,000000</strong>
                                </div>

                                <hr>

                                <div class="memoria-linha">
                                    <span>Custo Financeiro C.I./Imp./O.V.</span>
                                    <strong id="memoria_valor_custo_financeiro">R$ 0,00</strong>
                                </div>

                                <div class="memoria-linha">
                                    <span>Custo Financeiro Despesa Fixa</span>
                                    <strong id="memoria_valor_financeiro_despesa_fixa">R$ 0,00</strong>
                                </div>

                                <div class="memoria-linha">
                                    <span>Custo Financeiro IR/CSLL</span>
                                    <strong id="memoria_valor_financeiro_ir_csll">R$ 0,00</strong>
                                </div>

                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header fonte-12 negrito">
                                <i class="fas fa-chart-line"></i> Resultado
                            </div>

                            <div class="card-body">

                                <div class="memoria-linha memoria-total">
                                    <span>Margem de Contribuição</span>
                                    <strong>
                                        <span id="memoria_valor_mc">R$ 0,00</span>
                                        |
                                        <span id="memoria_percentual_mc">0,00%</span>
                                    </strong>
                                </div>

                                <div class="memoria-linha memoria-total mt-2">
                                    <span>Resultado Bruto</span>
                                    <strong>
                                        <span id="memoria_valor_rb">R$ 0,00</span>
                                        |
                                        <span id="memoria_percentual_rb">0,00%</span>
                                    </strong>
                                </div>

                                <div class="memoria-linha memoria-total mt-2">
                                    <span>Resultado Líquido</span>
                                    <strong>
                                        <span id="memoria_valor_liquido">R$ 0,00</span>
                                        |
                                        <span id="memoria_percentual_liquido">0,00%</span>
                                    </strong>
                                </div>

                            </div>
                        </div>

                        <div class="alert alert-info fonte-10">
                            <strong>Como o preço é formado?</strong>
                            <br>
                            O sistema usa a fórmula:
                            <br>
                            <code>Preço de Venda = Custo Total / Divisor</code>
                            <br><br>
                            O divisor considera impostos, comissão, frete, despesa fixa, DIFAL, IR/CSLL, outros custos,
                            taxa financeira, prazo médio e margem de lucro.
                        </div>

                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm fonte-12" data-dismiss="modal">
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>

<style>
    .modalMemoriaCalculo .modal-content {
        border-radius: 6px;
    }

    .modalMemoriaCalculo .modal-body {
        max-height: 78vh;
        overflow-y: auto;
        background: #f5f6f8;
    }

    .modalMemoriaCalculo .card {
        border-radius: 6px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.08);
    }

    .modalMemoriaCalculo .card-header {
        background: #f3f5f7;
        color: #426b60;
    }

    .modalMemoriaCalculo .memoria-card-resumo {
        min-height: 100px;
    }

    .modalMemoriaCalculo .memoria-card-resumo .card-body {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .modalMemoriaCalculo .memoria-card-resumo h5 {
        font-size: 20px;
        color: #6b7280;
    }

    .modalMemoriaCalculo .bg-success h5,
    .modalMemoriaCalculo .bg-success small {
        color: #ffffff !important;
    }

    .modalMemoriaCalculo .memoria-linha {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 0;
        border-bottom: 1px solid #f1f1f1;
    }

    .modalMemoriaCalculo .memoria-linha span {
        color: #426b60;
    }

    .modalMemoriaCalculo .memoria-linha strong {
        color: #111827;
    }

    .modalMemoriaCalculo .memoria-total {
        background: #f8fafc;
        padding: 7px 8px;
        border-radius: 4px;
        border-bottom: none;
    }

    .modalMemoriaCalculo .memoria-progress {
        height: 5px;
        margin-bottom: 6px;
        background-color: #e9ecef;
    }

    .modalMemoriaCalculo code {
        color: #e83e8c;
        font-size: 10px;
    }

    .modalMemoriaCalculo .modal-header {
        background: #ffffff;
        border-bottom: 1px solid #e5e7eb;
    }

    .modalMemoriaCalculo .modal-title {
        color: #426b60;
    }
</style>
