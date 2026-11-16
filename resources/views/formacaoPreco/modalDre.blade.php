<div class="modal fade" id="modalDre" tabindex="-1" role="dialog" aria-labelledby="modalDreLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content fonte-10">
            <div class="modal-header bg-secondary text-white py-2">
                <h5 class="modal-title" id="modalDreLabel"><i class="fas fa-file-invoice-dollar"></i> DRE da venda <small id="dre_titulo_produto"></small></h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
            </div>

            <div class="modal-body" id="dre_area_impressao">
                <div id="dre_conferencia" class="mb-2"></div>
                <div id="dre_reforma" class="alert alert-info py-1 px-2 mb-2" style="display:none"></div>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-1" id="dre_tabela">
                        <thead class="thead-light">
                            <tr>
                                <th>Linha</th>
                                <th class="text-right">R$ <span class="dre-un-peca">por peça</span></th>
                                <th class="text-right">R$ <span class="dre-un-m2">por m²</span></th>
                                <th class="text-right">% s/ receita bruta</th>
                                <th>Base / fórmula</th>
                            </tr>
                        </thead>
                        <tbody id="dre_corpo"></tbody>
                    </table>
                </div>
                <small class="text-muted">Valores por peça/chapa (unidade de venda do preço) e convertidos por m²/m³ quando há dimensões. IBS/CBS são cobrados por fora do preço.</small>
            </div>

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-primary btn-sm" id="btn_imprimir_dre"><i class="fas fa-print"></i> Imprimir / PDF</button>
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-times"></i> Fechar</button>
            </div>
        </div>
    </div>
</div>
<style>
    #modalDre .dre-subtotal td { background: #eef2f7; font-weight: 600; }
    #modalDre .dre-total td { background: #d4edda; font-weight: 700; }
    #modalDre .dre-credito td:first-child { color: #155724; }
    #modalDre td.dre-num { text-align: right; white-space: nowrap; }
    #modalDre .dre-formula { color: #6c757d; font-size: 9px; }
    @media print {
        body * { visibility: hidden !important; }
        #modalDre, #modalDre * { visibility: visible !important; }
        #modalDre { position: absolute; left: 0; top: 0; width: 100%; overflow: visible !important; }
        #modalDre .modal-footer, #modalDre .close { display: none !important; }
    }
</style>
