<div class="modal fade" id="ModalEstruturaProduto" tabindex="-1" role="dialog" aria-labelledby="ModalEstruturaProdutoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content fonte-10">

            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fonte-14" id="ModalEstruturaProdutoLabel">
                    <i class="fas fa-sitemap"></i>
                    Estrutura do Produto
                    <span id="modal_estrutura_produto_titulo"></span>
                </h5>

                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body modal-estrutura-produto-body">

                <div class="row">

                    <div class="form-group col-md-12">
                        <div class="card card-parametros card-estrutura-mp">

                            <div class="card-header">
                                <div class="estrutura-header">
                                    <span>
                                        <i class="fas fa-cubes"></i>
                                        Matérias-primas
                                    </span>

                                    <strong id="estrutura_total_mp">
                                        Total: 0,00
                                    </strong>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive tabela-estrutura-modal">

                                    <table class="table table-sm table-bordered table-hover fonte-10 mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 90px;">Código</th>
                                                <th>Descrição</th>
                                                <th class="text-center" style="width: 60px;">Nível</th>
                                                <th class="text-right" style="width: 100px;">Quantidade</th>
                                                <th class="text-right" style="width: 110px;">Custo unit.</th>
                                                <th class="text-right" style="width: 110px;">Valor MP</th>
                                            </tr>
                                        </thead>

                                        <tbody id="estrutura_lista_mp">
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">
                                                    Nenhuma matéria-prima localizada.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="form-group col-md-12">
                        <div class="card card-parametros card-estrutura-op">

                            <div class="card-header">
                                <div class="estrutura-header">
                                    <span>
                                        <i class="fas fa-cogs"></i>
                                        Operacional
                                    </span>

                                    <strong id="estrutura_total_op">
                                        Total: 0,00
                                    </strong>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive tabela-estrutura-modal">

                                    <table class="table table-sm table-bordered table-hover fonte-10 mb-0">
                                        <thead>
                                            <tr>
                                                <th style="width: 90px;">Código</th>
                                                <th>Descrição</th>
                                                <th class="text-center" style="width: 60px;">Nível</th>
                                                <th class="text-right" style="width: 110px;">Base operacional</th>
                                                <th class="text-right" style="width: 110px;">Custo unit.</th>
                                                <th class="text-right" style="width: 110px;">Valor OP</th>
                                            </tr>
                                        </thead>

                                        <tbody id="estrutura_lista_op">
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">
                                                    Nenhum custo operacional localizado.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                </div>
                            </div>

                        </div>
                    </div>

                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm fonte-10" data-dismiss="modal">
                    <i class="fas fa-times"></i> Fechar
                </button>
            </div>

        </div>
    </div>
</div>

<style>
    .modal-estrutura-produto-body {
        max-height: 72vh;
        overflow: auto;
    }

    .tabela-estrutura-modal {
        max-height: 420px;
        overflow-y: auto;
    }

    .tabela-estrutura-modal table th {
        background-color: #f8f9fa;
        color: #374151;
        font-weight: 700;
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 2;
    }

    .tabela-estrutura-modal table td {
        vertical-align: middle;
    }

    .estrutura-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .card-estrutura-mp {
        border-left: 4px solid #28a745;
    }

    .card-estrutura-mp .card-header {
        color: #1f6f36;
        background-color: #f0faf3;
    }

    .card-estrutura-op {
        border-left: 4px solid #f0ad4e;
    }

    .card-estrutura-op .card-header {
        color: #8a5a00;
        background-color: #fff9eb;
    }
</style>
