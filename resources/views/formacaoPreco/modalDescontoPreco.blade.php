<div class="modal fade" id="ModalDescontoPreco" tabindex="-1" role="dialog" aria-labelledby="ModalDescontoPrecoLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content fonte-10">

            <div class="modal-header bg-warning">
                <h5 class="modal-title fonte-12" id="ModalDescontoPrecoLabel">
                    <i class="fas fa-percent"></i> Desconto sobre o preço de venda
                </h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <div class="row">
                    <div class="form-group col-md-6">
                        <label>Preço base:</label>
                        <input type="text" class="form-control form-control-sm alinhaDireita" id="desconto_preco_base" readonly>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Tipo de desconto:</label>
                        <select class="form-control form-control-sm fonte-10" id="desconto_tipo">
                            <option value="P">Percentual</option>
                            <option value="V">Valor R$</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-6" id="box_desconto_percentual">
                        <label>% Desconto:</label>
                        <input type="text" class="form-control form-control-sm alinhaDireita mask-money" id="desconto_percentual" placeholder="0,00" autocomplete="off">
                    </div>

                    <div class="form-group col-md-6" id="box_desconto_valor">
                        <label>Valor Desconto:</label>
                        <input type="text" class="form-control form-control-sm alinhaDireita mask-money" id="desconto_valor" placeholder="0,00" autocomplete="off">
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-12">
                        <label>Preço final:</label>
                        <input type="text" class="form-control form-control-sm alinhaDireita campo-preco-final-desconto" id="desconto_preco_final" readonly>
                    </div>
                </div>

                <small class="text-muted">
                    O preço final será aplicado no campo Preço R$ e o tipo de cálculo será alterado para Preço.
                </small>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm fonte-10" data-dismiss="modal">
                    <i class="fas fa-times"></i> Fechar
                </button>

                <button type="button" class="btn btn-success btn-sm fonte-10" id="aplicar_desconto_preco">
                    <i class="fas fa-check"></i> Aplicar desconto
                </button>
            </div>

        </div>
    </div>
</div>
<style>
    .campo-preco-final-desconto {
        background: #eaffea !important;
        color: #198754 !important;
        font-weight: 700;
    }
</style>
