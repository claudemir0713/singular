<div class="modal fade ModalParamentros" id="ModalParamentros" tabindex="-1" role="dialog" aria-labelledby="ModalParamentrosLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content fonte-10">

            <div class="panel panel-primary">
                <div class="panel-heading bg-primary text-white">
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>

                    <h4 class="modal-title fonte-16" id="ModalParamentrosLabel">
                        <i class="fas fa-donate"></i> Parâmetros
                    </h4>
                </div>
            </div>

            <div class="modal-body">
                <form id="form_modal_parametros">
                    @csrf

                    <div class="row">

                        <div class="form-group col-md-4">
                            <label for="md_margem">Margem:</label>
                            <input class="form-control alinhaDireita atualiza_md_parametro fonte-10" type="text" step="any" name="md_margem" id="md_margem" title="Percentual de margem desejada para formação do preço de venda" value="{{ number_format($parametro->margem ?? 0, 2, ',', '.') }}"
                                inputmode="decimal"
                                pattern="[0-9]*[,.]?[0-9]*"
                                autocomplete="off"
                                autofocus>
                        </div>

                        <div class="form-group col-md-4">
                            <label for="md_parametro_taxa_financeira">Taxa Financeira:</label>
                            <input class="form-control alinhaDireita atualiza_md_parametro fonte-10" type="text" step="any" name="md_parametro_taxa_financeira" id="md_parametro_taxa_financeira" title="Taxa financeira mensal utilizada para cálculo do custo financeiro conforme o prazo médio" value="{{ number_format($parametro->taxa_financeira ?? 0, 2, ',', '.') }}"
                                inputmode="decimal"
                                pattern="[0-9]*[,.]?[0-9]*"
                                autocomplete="off"
                            >
                        </div>

                        <div class="form-group col-md-4">
                            <label for="md_parametro_icms">ICMS:</label>
                            <input class="form-control alinhaDireita atualiza_md_parametro fonte-10" type="text" step="any" name="md_parametro_icms" id="md_parametro_icms" title="Percentual de ICMS aplicado no cálculo dos impostos sobre a venda" value="{{ number_format($parametro->icms ?? 0, 2, ',', '.') }}"
                                inputmode="decimal"
                                pattern="[0-9]*[,.]?[0-9]*"
                                autocomplete="off"
                            >
                        </div>

                        <div class="form-group col-md-4">
                            <label for="md_parametro_simples">Simples Nacional:</label>
                            <input class="form-control alinhaDireita atualiza_md_parametro fonte-10" type="text" step="any" name="md_parametro_simples" id="md_parametro_simples" title="Percentual do Simples Nacional aplicado no cálculo dos impostos sobre a venda" value="{{ number_format($parametro->simples ?? 0, 2, ',', '.') }}"
                                inputmode="decimal"
                                pattern="[0-9]*[,.]?[0-9]*"
                                autocomplete="off"
                            >
                        </div>

                        <div class="form-group col-md-4">
                            <label for="md_parametro_pis">PIS:</label>
                            <input class="form-control alinhaDireita atualiza_md_parametro fonte-10" type="text" step="any" name="md_parametro_pis" id="md_parametro_pis" title="Percentual de PIS aplicado no cálculo dos impostos sobre a venda" value="{{ number_format($parametro->pis ?? 0, 2, ',', '.') }}"
                                inputmode="decimal"
                                pattern="[0-9]*[,.]?[0-9]*"
                                autocomplete="off"
                            >
                        </div>

                        <div class="form-group col-md-4">
                            <label for="md_parametro_cofins">COFINS:</label>
                            <input class="form-control alinhaDireita atualiza_md_parametro fonte-10" type="text" step="any" name="md_parametro_cofins" id="md_parametro_cofins" title="Percentual de COFINS aplicado no cálculo dos impostos sobre a venda" value="{{ number_format($parametro->cofins ?? 0, 2, ',', '.') }}"
                                inputmode="decimal"
                                pattern="[0-9]*[,.]?[0-9]*"
                                autocomplete="off"
                            >
                        </div>

                        <div class="form-group col-md-4">
                            <label for="md_parametro_ir_csll">IR/CSLL Sobre o Lucro:</label>
                            <input class="form-control alinhaDireita atualiza_md_parametro fonte-10" type="text" step="any" name="md_parametro_ir_csll" id="md_parametro_ir_csll" title="Percentual de IR e CSLL aplicado sobre o resultado no cálculo da formação de preço" value="{{ number_format($parametro->ir_csll ?? 0, 2, ',', '.') }}"
                                inputmode="decimal"
                                pattern="[0-9]*[,.]?[0-9]*"
                                autocomplete="off"
                            >
                        </div>

                        <div class="form-group col-md-4">
                            <label for="md_comissao">Comissão:</label>
                            <input class="form-control alinhaDireita atualiza_md_parametro fonte-10" type="text" step="any" name="md_comissao" id="md_comissao" title="Percentual de comissão aplicado como despesa variável na formação do preço de venda" value="{{ number_format($parametro->comissao ?? 0, 2, ',', '.') }}"
                                inputmode="decimal"
                                pattern="[0-9]*[,.]?[0-9]*"
                                autocomplete="off"
                            >
                        </div>

                        <div class="form-group col-md-4">
                            <label for="md_parametro_difal">DIFAL:</label>
                            <input class="form-control alinhaDireita atualiza_md_parametro fonte-10" type="text" step="any" name="md_parametro_difal" id="md_parametro_difal" title="Percentual de DIFAL aplicado como despesa variável na formação do preço de venda" value="{{ number_format($parametro->difal ?? 0, 2, ',', '.') }}"
                                inputmode="decimal"
                                pattern="[0-9]*[,.]?[0-9]*"
                                autocomplete="off"
                            >
                        </div>

                        <div class="form-group col-md-4">
                            <label for="md_parametro_frete_percentual">Frete %:</label>
                            <input class="form-control alinhaDireita atualiza_md_parametro fonte-10" type="text" step="any" name="md_parametro_frete_percentual" id="md_parametro_frete_percentual" title="Percentual de frete aplicado como despesa variável sobre o preço de venda" value="{{ number_format($parametro->frete ?? 0, 2, ',', '.') }}"
                                inputmode="decimal"
                                pattern="[0-9]*[,.]?[0-9]*"
                                autocomplete="off"
                            >
                        </div>

                        <div class="form-group col-md-4">
                            <label for="md_parametro_despesa_fixa">Despesa Fixa:</label>
                            <input class="form-control alinhaDireita atualiza_md_parametro fonte-10" type="text" step="any" name="md_parametro_despesa_fixa" id="md_parametro_despesa_fixa" title="Percentual de despesa fixa ou indireta utilizado na formação do preço de venda" value="{{ number_format($parametro->despesa_fixa ?? 0, 2, ',', '.') }}"
                                inputmode="decimal"
                                pattern="[0-9]*[,.]?[0-9]*"
                                autocomplete="off"
                            >
                        </div>

                        <div class="form-group col-md-4">
                            <label for="md_parametro_outros">Outros:</label>
                            <input class="form-control alinhaDireita atualiza_md_parametro fonte-10" type="text" step="any" name="md_parametro_outros" id="md_parametro_outros" title="Percentual de outras despesas variáveis aplicado na formação do preço de venda" value="{{ number_format($parametro->outros ?? 0, 2, ',', '.') }}"
                                inputmode="decimal"
                                pattern="[0-9]*[,.]?[0-9]*"
                                autocomplete="off"
                            >
                        </div>

                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <div class="row w-100">

                    <div class="form-group col-md-4">
                        <button type="button" class="btn btn-success btn-block btn-sm fonte-10" id="md_grava" data-dismiss="modal">
                            <i class="fa fa-check"></i> Gravar padrão
                        </button>
                    </div>


                    <div class="form-group col-md-4">
                        <button type="button" class="btn btn-info btn-block btn-sm fonte-10" id="md_simular" data-dismiss="modal">
                            <i class="fas fa-calculator"></i> Simular
                        </button>
                    </div>

                    <div class="form-group col-md-4">
                        <button type="button" class="btn btn-danger btn-block btn-sm fonte-10" data-dismiss="modal">
                            <i class="fa fa-times"></i> Fechar
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
