<div class="modal fade" id="ModalOrcamento" tabindex="-1" role="dialog" aria-labelledby="ModalOrcamentoPrecoLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content fonte-10">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fonte-12" id="ModalOrcamentoPrecoLabel">
                    <i class="fa fa-save"></i> Dados do orçamento
                </h5>

                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="form-group col-md-4">
                        <label>Data:</label>
                        <input type="date" class="form-control fonte-10" id="md_orcamento_data" name="md_orcamento_data">
                    </div>

                    <div class="form-group col-md-4">
                        <label>Nº:</label>
                        <input type="text" class="form-control fonte-10" id="md_orcamento_numero" name="md_orcamento_numero">
                    </div>

                    <div class="form-group col-md-12">
                        <label>Cliente:</label>
                        <input type="text" class="form-control fonte-10" id="md_orcamento_cliente_nome" name="md_orcamento_cliente_nome" placeholder="Informe o nome do cliente">
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-success btn-sm fonte-10" id="md_confirmar_salvar_orcamento">
                    <i class="fa fa-check"></i> Confirmar e salvar
                </button>

                <button type="button" class="btn btn-danger btn-sm fonte-10" data-dismiss="modal">
                    <i class="fa fa-times"></i> Cancelar
                </button>
            </div>
        </div>
    </div>
</div>
