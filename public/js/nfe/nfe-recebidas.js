/* NF-e Recebidas: certificado A1, busca na SEFAZ, ciencia da operacao e download de XML. Usa jQuery + SweetAlert2 do layout. */
(function ($) {
    var R = window.NFE_ROTAS;
    var pagina = 1;
    var selecionadas = {};   // chave -> estado
    var linhas = {};         // chave -> documento da pagina atual

    var ROTULO = {
        sem_ciencia: '<span class="badge badge-secondary">Sem ciência</span>',
        aguardando: '<span class="badge badge-warning">Aguardando XML</span>',
        com_xml: '<span class="badge badge-success">XML disponível</span>'
    };

    function esc(v) { return $('<div>').text(v == null ? '' : v).html(); }
    function moeda(v) { return Number(v || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' }); }
    function data(v) { return v ? v.substr(8, 2) + '/' + v.substr(5, 2) + '/' + v.substr(0, 4) : ''; }
    function cnpj(c) { return c && c.length === 14 ? c.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5') : (c || ''); }
    function erro(xhr) {
        var j = xhr.responseJSON;
        return (j && j.erros && j.erros.join(' ')) || (j && j.message) || 'Falha de comunicação com o servidor.';
    }
    function ajax(opts) {
        opts.headers = { 'X-CSRF-TOKEN': R.csrf, 'Accept': 'application/json' };
        return $.ajax(opts);
    }

    /* ---------------------------------------------------------- situacao do certificado */
    function mostrarStatus(s) {
        var box = $('#statusCert');
        if (!s || !s.configurado) {
            box.attr('class', 'alert alert-warning').html('<i class="fas fa-exclamation-triangle"></i> Nenhum certificado A1 cadastrado. Use o botão <b>Certificado A1</b> para cadastrar.');
            $('#infoCert').html('<div class="text-muted">Nenhum certificado cadastrado.</div>');
            $('#btnRemoverCert').hide();
            return;
        }
        var cls = s.valido ? (s.dias_restantes <= 30 ? 'alert-warning' : 'alert-success') : 'alert-danger';
        var txt = '<i class="fas fa-certificate"></i> <b>' + esc(s.titular) + '</b> &middot; CNPJ ' + cnpj(s.cnpj) +
            ' &middot; ' + (s.valido ? 'válido até ' + data(s.valido_ate) + ' (' + s.dias_restantes + ' dias)' : esc(s.motivo)) +
            ' &middot; ' + (s.ambiente === 2 ? 'Homologação' : 'Produção');
        if (s.ultima_consulta) {
            txt += '<br><small>Última consulta: ' + esc(s.ultima_consulta) + (s.ultimo_status ? ' &mdash; ' + esc(s.ultimo_status) : '') +
                (s.liberada ? '' : ' &middot; próxima liberada em ' + esc(s.proxima_consulta)) + '</small>';
        }
        box.attr('class', 'alert ' + cls).html(txt);
        $('#infoCert').html('<div class="alert ' + cls + ' mb-0">' + txt + '</div>');
        $('#btnRemoverCert').show();
    }

    function carregarStatus() {
        return ajax({ url: R.status, type: 'GET' }).done(mostrarStatus);
    }

    /* ---------------------------------------------------------- lista */
    function filtros() {
        return { busca: $('#fBusca').val(), de: $('#fDe').val(), ate: $('#fAte').val(), situacao: $('#fSituacao').val() };
    }

    function atualizarBotoes() {
        var ciencia = 0, zip = 0;
        $.each(selecionadas, function (k, st) { if (st === 'sem_ciencia') { ciencia++; } if (st === 'com_xml') { zip++; } });
        $('#qtdCiencia').text(ciencia); $('#btnCiencia').prop('disabled', !ciencia);
        $('#qtdZip').text(zip); $('#btnZip').prop('disabled', !zip);
    }

    function carregar(p) {
        pagina = p || 1;
        $('#corpoNfe').html('<tr><td colspan="8" class="text-center text-muted">Carregando...</td></tr>');
        ajax({ url: R.documentos, type: 'GET', data: $.extend(filtros(), { pagina: pagina }) }).done(function (r) {
            var h = '';
            linhas = {};
            $.each(r.documentos, function (i, d) {
                linhas[d.chave] = d;
                var marc = selecionadas[d.chave] ? ' checked' : '';
                var acoes = '';
                if (d.estado === 'com_xml') {
                    acoes = '<a class="btn btn-sm btn-primary" title="Baixar XML" target="_blank" href="' + R.xml + '/' + d.chave + '"><i class="fas fa-download"></i></a>';
                } else if (d.estado === 'aguardando') {
                    acoes = '<button class="btn btn-sm btn-outline-primary btn-buscar" data-chave="' + d.chave + '" title="Buscar o XML desta nota na SEFAZ"><i class="fas fa-search"></i></button>';
                } else {
                    acoes = '<button class="btn btn-sm btn-warning btn-ciencia" data-chave="' + d.chave + '" title="Dar ciência da operação"><i class="fas fa-eye"></i></button>';
                }
                h += '<tr><td><input type="checkbox" class="chk" data-chave="' + d.chave + '"' + marc + '></td>' +
                    '<td>' + data(d.emissao) + '</td><td>' + esc(d.numero) + '</td><td>' + esc(d.emitente) + '</td><td>' + cnpj(d.cnpj) + '</td>' +
                    '<td class="text-right">' + moeda(d.valor) + '</td><td>' + ROTULO[d.estado] + '</td><td class="text-center">' + acoes + '</td></tr>';
            });
            $('#corpoNfe').html(h || '<tr><td colspan="8" class="text-center text-muted">Nenhuma nota encontrada. Use "Buscar na SEFAZ".</td></tr>');
            var c = r.contagem || {};
            $('#resumoLista').text(r.total + ' nota(s) · sem ciência: ' + (c.sem_ciencia || 0) + ' · aguardando XML: ' + (c.aguardando || 0) + ' · com XML: ' + (c.com_xml || 0));
            paginar(r.pagina, r.paginas);
            $('#chkTodos').prop('checked', false);
            atualizarBotoes();
            if (r.status_cert) { mostrarStatus(r.status_cert); }
        }).fail(function (xhr) {
            $('#corpoNfe').html('<tr><td colspan="8" class="text-danger text-center">' + esc(erro(xhr)) + '</td></tr>');
        });
    }

    function paginar(atual, total) {
        var h = '';
        if (total > 1) {
            var ini = Math.max(1, atual - 3), fim = Math.min(total, atual + 3);
            h += '<li class="page-item' + (atual === 1 ? ' disabled' : '') + '"><a class="page-link" href="#" data-p="' + (atual - 1) + '">&laquo;</a></li>';
            for (var i = ini; i <= fim; i++) { h += '<li class="page-item' + (i === atual ? ' active' : '') + '"><a class="page-link" href="#" data-p="' + i + '">' + i + '</a></li>'; }
            h += '<li class="page-item' + (atual === total ? ' disabled' : '') + '"><a class="page-link" href="#" data-p="' + (atual + 1) + '">&raquo;</a></li>';
        }
        $('#paginacao').html(h);
    }

    /* ---------------------------------------------------------- ciencia */
    function darCiencia(chaves, forcar) {
        if (!chaves.length) { return; }
        Swal.fire({
            title: 'Dar ciência da operação?',
            html: chaves.length + ' nota(s). A ciência é um evento fiscal registrado na SEFAZ e libera o XML completo.',
            icon: 'question', showCancelButton: true, confirmButtonText: 'Dar ciência', cancelButtonText: 'Cancelar'
        }).then(function (res) {
            if (!res.value && !res.isConfirmed) { return; }
            Swal.fire({ title: 'Registrando ciência...', allowOutsideClick: false, onBeforeOpen: function () { Swal.showLoading(); }, didOpen: function () { Swal.showLoading(); } });
            // a API aceita ate 50 chaves por chamada: envia em lotes
            var lotes = [], i;
            for (i = 0; i < chaves.length; i += 50) { lotes.push(chaves.slice(i, i + 50)); }
            var total = { registradas: 0, ignoradas: 0, falhas: [], interrompido: null };
            (function proximo(n) {
                if (n >= lotes.length || total.interrompido) { return fim(); }
                ajax({ url: R.ciencia, type: 'POST', data: { chaves: lotes[n], forcar: forcar ? 1 : 0 } }).done(function (r) {
                    total.registradas += r.registradas.length; total.ignoradas += r.ignoradas.length;
                    $.each(r.falhas || {}, function (k, m) { total.falhas.push(k.substr(25, 9).replace(/^0+/, '') + ': ' + m); });
                    total.interrompido = r.interrompido;
                    proximo(n + 1);
                }).fail(function (xhr) { total.interrompido = erro(xhr); fim(); });
            })(0);
            function fim() {
                var html = 'Ciência registrada: <b>' + total.registradas + '</b>' + (total.ignoradas ? ' · já registradas: ' + total.ignoradas : '');
                if (total.falhas.length) { html += '<br><small class="text-danger">' + total.falhas.slice(0, 8).map(esc).join('<br>') + '</small>'; }
                if (total.interrompido) { html += '<br><span class="text-danger">' + esc(total.interrompido) + '</span>'; }
                if (total.registradas) { html += '<br><br>Agora clique em <b>Buscar na SEFAZ</b> para receber os XMLs.'; }
                Swal.fire({ title: total.interrompido ? 'Interrompido' : 'Concluído', html: html, icon: total.interrompido ? 'warning' : 'success' });
                selecionadas = {};
                carregar(pagina);
            }
        });
    }

    /* ---------------------------------------------------------- eventos */
    $(function () {
        // padrao da tela: ultimos 15 dias (menos itens); "Limpar" volta para ele
        function periodoPadrao() {
            var hoje = new Date(), ini = new Date();
            ini.setDate(ini.getDate() - 15);
            $('#fDe').val(iso(ini)); $('#fAte').val(iso(hoje));
        }
        periodoPadrao();
        carregarStatus();
        carregar(1);

        $('#formFiltro').on('submit', function () { selecionadas = {}; carregar(1); return false; });
        $('#btnLimpar').on('click', function () { $('#fBusca').val(''); $('#fSituacao').val(''); periodoPadrao(); selecionadas = {}; carregar(1); });
        $('#paginacao').on('click', 'a', function (e) { e.preventDefault(); var p = parseInt($(this).data('p'), 10); if (p) { carregar(p); } });

        $('#corpoNfe').on('change', '.chk', function () {
            var k = $(this).data('chave');
            if (this.checked) { selecionadas[k] = linhas[k].estado; } else { delete selecionadas[k]; }
            atualizarBotoes();
        });
        $('#chkTodos').on('change', function () {
            var on = this.checked;
            $('#corpoNfe .chk').each(function () { $(this).prop('checked', on).trigger('change'); });
        });

        $('#corpoNfe').on('click', '.btn-ciencia', function () { darCiencia([$(this).data('chave')]); });
        $('#btnCiencia').on('click', function () {
            var ch = []; $.each(selecionadas, function (k, st) { if (st === 'sem_ciencia') { ch.push(k); } });
            darCiencia(ch);
        });

        $('#corpoNfe').on('click', '.btn-buscar', function () {
            var b = $(this), chave = b.data('chave'), icone = b.html();
            b.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
            ajax({ url: R.buscarChave, type: 'POST', data: { chave: chave } }).done(function (r) {
                if (r.xml) {
                    carregar(pagina);
                    return Swal.fire({ icon: 'success', title: 'XML recebido', text: 'A nota já pode ser baixada.', timer: 2200, showConfirmButton: false });
                }
                b.prop('disabled', false).html(icone);
                var recebeuResumo = (r.recebidos || []).some(function (s) { return /resNFe/i.test(s); });
                var titulo = 'O XML ainda não foi liberado', corpo, refazer = false;
                if (r.cStat === '138') {
                    corpo = 'A SEFAZ encontrou a nota, mas ' + (recebeuResumo ? 'entregou só o <b>resumo</b>' : 'ainda não entregou o XML completo') + '.' +
                        '<br><br>Isso é normal logo após a ciência: o XML costuma sair em alguns minutos, às vezes até 1 hora. Tente de novo mais tarde.' +
                        '<br><br><small class="text-muted">Se a ciência foi dada há muito tempo e o XML não vem, ela pode não ter sido registrada na SEFAZ: use <b>Dar ciência novamente</b>.</small>';
                    refazer = true;
                } else if (r.cStat === '137') {
                    titulo = 'Nota não localizada';
                    corpo = 'A SEFAZ não encontrou documento para esta chave. Confira se a nota foi emitida contra o CNPJ da empresa.';
                } else if (r.cStat === '656') {
                    titulo = 'Consulta bloqueada pela SEFAZ';
                    corpo = 'Muitas consultas em pouco tempo. Aguarde cerca de 1 hora e tente novamente.';
                } else {
                    corpo = 'Resposta da SEFAZ: ' + esc((r.cStat || '') + ' - ' + (r.motivo || 'sem detalhes')) + '.';
                }
                if (r.cStat === '138' && r.motivo) { corpo += '<br><small class="text-muted">Retorno da SEFAZ: ' + esc(r.cStat + ' - ' + r.motivo) + '</small>'; }
                Swal.fire({
                    icon: 'info', title: titulo, html: corpo, confirmButtonText: 'Entendi',
                    showDenyButton: refazer, denyButtonText: 'Dar ciência novamente', denyButtonColor: '#f0ad4e'
                }).then(function (res) { if (res.isDenied) { darCiencia([chave], true); } });
            }).fail(function (xhr) {
                b.prop('disabled', false).html(icone);
                Swal.fire({ icon: 'error', title: 'Não foi possível consultar', text: erro(xhr) });
            });
        });

        $('#btnZip').on('click', function () {
            var f = $('<form method="POST" action="' + R.zip + '" style="display:none;"></form>');
            f.append('<input type="hidden" name="_token" value="' + R.csrf + '">');
            $.each(selecionadas, function (k, st) { if (st === 'com_xml') { f.append($('<input type="hidden" name="chaves[]">').val(k)); } });
            $('body').append(f); f.trigger('submit'); setTimeout(function () { f.remove(); }, 2000);
        });

        $('#btnConsultar').on('click', function () {
            var b = $(this).prop('disabled', true);
            Swal.fire({ title: 'Consultando a SEFAZ...', text: 'Pode levar alguns segundos.', allowOutsideClick: false, onBeforeOpen: function () { Swal.showLoading(); }, didOpen: function () { Swal.showLoading(); } });
            ajax({ url: R.consultar, type: 'POST' }).done(function (r) {
                var n = r.novos || {};
                Swal.fire({
                    icon: 'success', title: 'Consulta concluída',
                    html: 'Resumos: <b>' + n.resNFe + '</b> · XML completos: <b>' + n.procNFe + '</b> · Eventos: <b>' + n.eventos + '</b>' + (r.aviso ? '<br><small>' + esc(r.aviso) + '</small>' : '')
                });
                mostrarStatus(r.status_cert); carregar(1);
            }).fail(function (xhr) {
                Swal.fire({ icon: 'error', title: 'Consulta não realizada', text: erro(xhr) });
                if (xhr.responseJSON && xhr.responseJSON.status_cert) { mostrarStatus(xhr.responseJSON.status_cert); }
            }).always(function () { b.prop('disabled', false); });
        });

        /* importar do ERP: modal com periodo de emissao */
        function iso(d) { return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2); }
        function periodo(dias) {
            var hoje = new Date();
            if (dias > 0) { var ini = new Date(); ini.setDate(ini.getDate() - dias); $('#erpDe').val(iso(ini)); $('#erpAte').val(iso(hoje)); }
            else { $('#erpDe,#erpAte').val(''); }
            $('.btn-periodo').removeClass('active').filter('[data-dias="' + dias + '"]').addClass('active');
            dicaErp();
        }
        function dicaErp() {
            var de = $('#erpDe').val(), ate = $('#erpAte').val();
            $('#erpDica').text(!de && !ate ? 'Sem período: importa tudo o que o ERP tiver (a primeira vez pode levar alguns minutos).' : 'Só entram notas emitidas dentro do período.');
        }
        $('#btnImportarErp').on('click', function () { $('#erpErro').hide(); periodo(30); $('#modalErp').modal('show'); });
        $('.btn-periodo').on('click', function () { periodo(parseInt($(this).data('dias'), 10)); });
        $('#erpDe,#erpAte').on('change', function () { $('.btn-periodo').removeClass('active'); dicaErp(); });
        $('#btnErpImportar').on('click', function () {
            var de = $('#erpDe').val(), ate = $('#erpAte').val();
            if (de && ate && de > ate) { return $('#erpErro').text('A data inicial não pode ser maior que a final.').show(); }
            $('#erpErro').hide();
            var b = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Importando...');
            ajax({ url: R.importarErp, type: 'POST', timeout: 0, data: { de: de, ate: ate } }).done(function (r) {
                $('#modalErp').modal('hide');
                var importados = r.resumos + r.completos, atualizados = r.ciencias || 0;
                var linha = function (rotulo, valor, cor) { return '<tr><td class="text-left">' + rotulo + '</td><td class="text-right"><b class="' + (cor || '') + '">' + valor + '</b></td></tr>'; };
                var html = '<table class="table table-sm mb-2 mx-auto" style="max-width:340px;">' +
                    linha('Lidos no ERP', r.lidos) +
                    linha('Importados (novos)', importados, importados ? 'text-success' : 'text-muted') +
                    linha('&nbsp;&nbsp;<small>resumos</small>', '<small>' + r.resumos + '</small>') +
                    linha('&nbsp;&nbsp;<small>XML completos</small>', '<small>' + r.completos + '</small>') +
                    linha('Atualizados (ciência)', atualizados, atualizados ? 'text-primary' : 'text-muted') +
                    linha('Já existiam, sem mudança', r.ignorados, 'text-muted') + '</table>';
                html += '<small class="text-muted">' + (de || ate ? 'Período: ' + (de ? data(de) : 'início') + ' a ' + (ate ? data(ate) : 'hoje') : 'Sem filtro de período') + '</small>';
                Swal.fire({ icon: (importados || atualizados) ? 'success' : 'info', title: (importados || atualizados) ? 'Importação concluída' : 'Nada novo para importar', html: html });
                carregarStatus(); carregar(1);
            }).fail(function (xhr) { $('#erpErro').html('<i class="fas fa-exclamation-circle"></i> ' + esc(erro(xhr))).show(); })
              .always(function () { b.prop('disabled', false).html('<i class="fas fa-download"></i> Importar'); });
        });

        /* certificado */
        var BTN_SALVAR = '<i class="fas fa-save"></i> Salvar certificado';
        function certPronto() {
            $('#btnSalvarCert').prop('disabled', !($('#certArquivo')[0].files.length && $('#certSenha').val()));
        }
        function certReset() {
            $('#certArquivo').val(''); $('#certSenha').val('').attr('type', 'password'); $('#btnVerSenha i').attr('class', 'fas fa-eye');
            $('#certNome').html('Clique para escolher o arquivo <b>.pfx</b> ou <b>.p12</b>'); $('#certTamanho').text('');
            $('#certIcone').attr('class', 'fas fa-file-upload fa-2x text-muted d-block mb-1');
            $('#certErro').hide().text('');
            $('#btnSalvarCert').html(BTN_SALVAR);
            certPronto();
        }
        $('#btnCertificado').on('click', function () { certReset(); $('#modalCertificado').modal('show'); });
        $('#certArquivo').on('change', function () {
            var f = this.files[0];
            $('#certErro').hide();
            if (!f) { return certReset(); }
            if (!/\.(pfx|p12)$/i.test(f.name)) {
                this.value = '';
                $('#certErro').text('O certificado A1 precisa ser um arquivo .pfx ou .p12.').show();
                return certPronto();
            }
            $('#certNome').text(f.name);
            $('#certTamanho').text((f.size / 1024).toFixed(1).replace('.', ',') + ' KB');
            $('#certIcone').attr('class', 'fas fa-file-signature fa-2x text-success d-block mb-1');
            certPronto();
            $('#certSenha').trigger('focus');
        });
        $('#certSenha').on('input', certPronto).on('keydown', function (e) { if (e.which === 13) { e.preventDefault(); $('#btnSalvarCert').trigger('click'); } });
        $('#btnVerSenha').on('click', function () {
            var s = $('#certSenha'), ver = s.attr('type') === 'password';
            s.attr('type', ver ? 'text' : 'password'); $(this).find('i').attr('class', ver ? 'fas fa-eye-slash' : 'fas fa-eye');
        });
        $('#btnSalvarCert').on('click', function () {
            var arq = $('#certArquivo')[0].files[0];
            if (!arq || !$('#certSenha').val()) { return; }
            var fd = new FormData();
            fd.append('certificado', arq); fd.append('senha', $('#certSenha').val()); fd.append('ambiente', $('input[name=certAmbiente]:checked').val());
            var b = $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Validando...');
            $('#certErro').hide();
            ajax({ url: R.certificado, type: 'POST', data: fd, processData: false, contentType: false }).done(function (r) {
                mostrarStatus(r); $('#modalCertificado').modal('hide'); certReset();
                Swal.fire({ icon: 'success', title: 'Certificado cadastrado', text: 'Agora clique em "Buscar na SEFAZ".' });
            }).fail(function (xhr) {
                $('#certErro').html('<i class="fas fa-exclamation-circle"></i> ' + esc(erro(xhr))).show();
                $('#certSenha').trigger('select');
            }).always(function () { b.html(BTN_SALVAR); certPronto(); });
        });
        $('#btnRemoverCert').on('click', function () {
            Swal.fire({ title: 'Remover o certificado?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Remover', cancelButtonText: 'Cancelar' }).then(function (res) {
                if (!res.value && !res.isConfirmed) { return; }
                ajax({ url: R.certificado, type: 'DELETE' }).done(function (r) { mostrarStatus(r); $('#modalCertificado').modal('hide'); });
            });
        });
    });
})(jQuery);
