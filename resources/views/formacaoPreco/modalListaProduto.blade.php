<div class="modal fade" id="ModalProdutos" tabindex="-1" role="dialog" aria-labelledby="ModalProdutosLabel">

    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content fonte-10">

            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title fonte-16" id="ModalProdutosLabel">
                    <i class="fas fa-search"></i>
                    Selecione o Produto
                </h4>

                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label for="modal_pesquisa_produto">
                        Pesquisar nos resultados:
                    </label>

                    <div class="input-group">
                        <input type="text" class="form-control fonte-10" id="modal_pesquisa_produto"
                            placeholder="Digite código ou parte do nome" autocomplete="off">

                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default" id="btn_limpar_pesquisa_modal">
                                <i class="fa fa-times"></i>
                            </button>
                        </span>
                    </div>
                </div>

                <div id="modal_sem_resultados" class="alert alert-warning fonte-10" style="display: none;">
                    Nenhum produto encontrado com esse filtro.
                </div>

                <div id="modal_lista_produtos" class="list-group" style="max-height: 450px; overflow-y: auto;">
                </div>
                <div class="form-group">
                    <label>Produtos encontrados:</label>

                    <div id="modal_lista_produtos" class="list-group" style="max-height: 450px; overflow-y: auto;">
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal">
                    <i class="fa fa-times"></i>
                    Fechar
                </button>
            </div>

        </div>
    </div>
</div>
