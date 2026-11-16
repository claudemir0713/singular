@extends('layouts.model')
@section('content')

    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="60%">
                <h3><i class="fas fa-file-invoice"></i> NF-e Recebidas</h3>
                <small class="text-muted">Dar ciência da operação e baixar os XMLs das notas emitidas contra o CNPJ da empresa.</small>
            </td>
            <td class="text-right">
                <button type="button" class="btn btn-light border" id="btnCertificado"><i class="fas fa-lock"></i> Certificado A1</button>
                <button type="button" class="btn btn-light border" id="btnImportarErp" title="Traz do ERP Singular as notas, XMLs e ciências já recebidos"><i class="fas fa-database"></i> Importar do ERP</button>
                <button type="button" class="btn btn-success" id="btnConsultar"><i class="fas fa-sync"></i> Buscar na SEFAZ</button>
            </td>
        </tr>
    </table>

    <div class="alert alert-secondary" id="statusCert">Carregando situação do certificado...</div>

    <div class="card card-body mb-3">
        <form id="formFiltro" onsubmit="return false;">
            <div class="row">
                <div class="form-group col-md-2">
                    Emissão de:
                    <input class="form-control" type="date" id="fDe">
                </div>
                <div class="form-group col-md-2">
                    Emissão até:
                    <input class="form-control" type="date" id="fAte">
                </div>
                <div class="form-group col-md-4">
                    Emitente / Nº NF / Chave:
                    <input class="form-control" type="text" id="fBusca" placeholder="Nome, CNPJ, número ou chave">
                </div>
                <div class="form-group col-md-2">
                    Situação:
                    <select class="form-control" id="fSituacao">
                        <option value="">Todas</option>
                        <option value="sem_ciencia">Sem ciência</option>
                        <option value="aguardando">Ciência dada, aguardando XML</option>
                        <option value="com_xml">Com XML</option>
                    </select>
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2" id="btnFiltrar"><i class="fas fa-search"></i> Filtrar</button>
                    <button type="button" class="btn btn-light border" id="btnLimpar">Limpar</button>
                </div>
            </div>
        </form>
    </div>

    <div class="mb-2">
        <button type="button" class="btn btn-warning" id="btnCiencia" disabled><i class="fas fa-eye"></i> Dar ciência (<span id="qtdCiencia">0</span>)</button>
        <button type="button" class="btn btn-primary" id="btnZip" disabled><i class="fas fa-file-archive"></i> Baixar XML em ZIP (<span id="qtdZip">0</span>)</button>
        <button type="button" class="btn btn-link" id="btnTodosFiltro" style="display:none;"></button>
        <span class="float-right text-muted" id="resumoLista"></span>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover table-sm" id="tabelaNfe">
            <thead>
                <tr>
                    <th width="30"><input type="checkbox" id="chkTodos"></th>
                    <th>Emissão</th>
                    <th>NF</th>
                    <th>Emitente</th>
                    <th>CNPJ</th>
                    <th class="text-right">Valor</th>
                    <th>Situação</th>
                    <th width="110" class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody id="corpoNfe"><tr><td colspan="8" class="text-center text-muted">Carregando...</td></tr></tbody>
        </table>
    </div>
    <nav><ul class="pagination pagination-sm" id="paginacao"></ul></nav>

    {{-- Importar do ERP --}}
    <div class="modal fade" id="modalErp" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-database"></i> Importar do ERP Singular</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3">Traz as notas, XMLs e ciências que o ERP já recebeu da SEFAZ. Escolha o período de emissão para não buscar tudo sempre.</p>
                    <div class="mb-3">
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-periodo" data-dias="7">Últimos 7 dias</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-periodo" data-dias="30">Últimos 30 dias</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-periodo" data-dias="90">Últimos 90 dias</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary btn-periodo" data-dias="0">Tudo</button>
                    </div>
                    <div class="row">
                        <div class="form-group col-6">
                            Emissão de:
                            <input type="date" class="form-control" id="erpDe">
                        </div>
                        <div class="form-group col-6">
                            Emissão até:
                            <input type="date" class="form-control" id="erpAte">
                        </div>
                    </div>
                    <small class="text-muted" id="erpDica"></small>
                    <div class="alert alert-danger mt-3 mb-0" id="erpErro" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnErpImportar"><i class="fas fa-download"></i> Importar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Certificado --}}
    <div class="modal fade" id="modalCertificado" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-lock"></i> Certificado digital A1</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div id="infoCert" class="mb-3"></div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold mb-1">1. Arquivo do certificado</label>
                        <label for="certArquivo" id="certDrop" class="d-block text-center border rounded p-3 mb-0" style="border-style:dashed !important; cursor:pointer; background:#fafafa;">
                            <i class="fas fa-file-upload fa-2x text-muted d-block mb-1" id="certIcone"></i>
                            <span id="certNome" class="d-block">Clique para escolher o arquivo <b>.pfx</b> ou <b>.p12</b></span>
                            <small id="certTamanho" class="text-muted"></small>
                        </label>
                        <input type="file" id="certArquivo" accept=".pfx,.p12" style="display:none;">
                    </div>

                    <div class="form-group mb-3">
                        <label for="certSenha" class="font-weight-bold mb-1">2. Senha do certificado</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="certSenha" autocomplete="new-password" placeholder="Senha definida na emissão do e-CNPJ">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="btnVerSenha" title="Mostrar/ocultar senha" tabindex="-1"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold mb-1">3. Ambiente</label>
                        <div class="btn-group btn-group-toggle d-flex" data-toggle="buttons">
                            <label class="btn btn-outline-primary active flex-fill"><input type="radio" name="certAmbiente" value="1" checked> Produção</label>
                            <label class="btn btn-outline-secondary flex-fill"><input type="radio" name="certAmbiente" value="2"> Homologação (testes)</label>
                        </div>
                        <small class="text-muted">Use <b>Produção</b> para receber as notas reais.</small>
                    </div>

                    <div class="alert alert-light border small mb-0">
                        <i class="fas fa-shield-alt text-success"></i> O arquivo é guardado fora da pasta pública e a senha é gravada criptografada.
                        O CNPJ do certificado precisa ser o da empresa desta base.
                    </div>

                    <div class="alert alert-danger mt-3 mb-0" id="certErro" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger mr-auto" id="btnRemoverCert" style="display:none;"><i class="fas fa-trash"></i> Remover</button>
                    <button type="button" class="btn btn-light border" data-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" id="btnSalvarCert" disabled><i class="fas fa-save"></i> Salvar certificado</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.NFE_ROTAS = {
            status: "{{ route('nfeRecebidas.status') }}",
            documentos: "{{ route('nfeRecebidas.documentos') }}",
            certificado: "{{ route('nfeRecebidas.certificado') }}",
            consultar: "{{ route('nfeRecebidas.consultar') }}",
            importarErp: "{{ route('nfeRecebidas.importarErp') }}",
            ciencia: "{{ route('nfeRecebidas.ciencia') }}",
            buscarChave: "{{ route('nfeRecebidas.buscarChave') }}",
            xml: "{{ url('nfeRecebidas/xml') }}",
            zip: "{{ route('nfeRecebidas.zip') }}",
            csrf: "{{ csrf_token() }}"
        };
    </script>
    <script src="{{ asset('js/nfe/nfe-recebidas.js') }}?v={{ filemtime(public_path('js/nfe/nfe-recebidas.js')) }}"></script>
@endsection
