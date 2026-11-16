<div class="modal fade" id="ModalHistoricoCustos" tabindex="-1" role="dialog" aria-labelledby="ModalHistoricoCustosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content fonte-10">

            <div class="modal-header bg-warning">
                <h5 class="modal-title fonte-14" id="ModalHistoricoCustosLabel">
                    <i class="fas fa-history"></i>
                    Histórico de custos
                    <span id="modal_historico_titulo"></span>
                </h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover table-bordered mb-0">
                        <thead>
                            <tr>
                                <th>Competência</th>
                                <th class="text-right">MP (chapa)</th>
                                <th class="text-right">OP (chapa)</th>
                                <th class="text-right">Custo total (chapa)</th>
                                <th class="text-right" id="th_historico_m2">Custo por m²</th>
                                <th class="text-right">Var. mês</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="historico_custos_lista">
                            <tr><td colspan="7" class="text-center text-muted">Nenhum custo localizado.</td></tr>
                        </tbody>
                    </table>
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
