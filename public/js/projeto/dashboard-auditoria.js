/* Auditoria dos cards do dashboard: lista os lançamentos que compõem cada valor. */
(function ($) {
    'use strict';

    var estado = { card: null, dados: null, ordem: null, asc: true, termo: '' };

    var fmtMoeda = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
    var fmtNum = new Intl.NumberFormat('pt-BR', { maximumFractionDigits: 2 });

    function esc(v) {
        return String(v === null || v === undefined ? '' : v).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function data(v) {
        if (!v) return '';
        var m = String(v).match(/^(\d{4})-(\d{2})-(\d{2})/);
        return m ? m[3] + '/' + m[2] + '/' + m[1] : String(v);
    }

    function formatar(col, v) {
        if (v === null || v === undefined || v === '') return '';
        if (col.t === 'moeda') return fmtMoeda.format(Number(v) || 0);
        if (col.t === 'data') return data(v);
        if (col.t === 'num') return fmtNum.format(Number(v) || 0);
        return String(v);
    }

    function valorOrdenacao(col, v) {
        if (v === null || v === undefined || v === '') return col.t === 'texto' ? '' : -Infinity;
        if (col.t === 'moeda' || col.t === 'num') return Number(v) || 0;
        return String(v).toLowerCase();
    }

    function linhasVisiveis() {
        var d = estado.dados;
        if (!d) return [];
        var linhas = d.linhas.slice();
        var termo = estado.termo;
        if (termo) {
            linhas = linhas.filter(function (l) {
                return d.colunas.some(function (c) {
                    return formatar(c, l[c.k]).toLowerCase().indexOf(termo) !== -1;
                });
            });
        }
        if (estado.ordem !== null) {
            var col = d.colunas[estado.ordem];
            var f = estado.asc ? 1 : -1;
            linhas.sort(function (a, b) {
                var x = valorOrdenacao(col, a[col.k]), y = valorOrdenacao(col, b[col.k]);
                if (typeof x === 'string') return x.localeCompare(y, 'pt-BR') * f;
                return (x - y) * f;
            });
        }
        return linhas;
    }

    function renderizar() {
        var d = estado.dados;
        if (!d) return;
        var linhas = linhasVisiveis();
        var total = linhas.reduce(function (s, l) { return s + (Number(l.VALOR) || 0); }, 0);

        var cab = '<tr>' + d.colunas.map(function (c, i) {
            var seta = estado.ordem === i ? (estado.asc ? ' <i class="fas fa-sort-up"></i>' : ' <i class="fas fa-sort-down"></i>') : ' <i class="fas fa-sort audit-sort-off"></i>';
            return '<th class="' + (c.t === 'moeda' || c.t === 'num' ? 'text-right' : '') + '" data-col="' + i + '">' + esc(c.l) + seta + '</th>';
        }).join('') + '</tr>';

        var corpo = linhas.length ? linhas.map(function (l) {
            return '<tr>' + d.colunas.map(function (c) {
                var txt = formatar(c, l[c.k]);
                var classes = [];
                if (c.t === 'moeda' || c.t === 'num') classes.push('text-right');
                if (c.t === 'texto') classes.push('audit-trunc');
                if (c.t === 'moeda' && Number(l[c.k]) < 0) classes.push('text-danger');
                if (c.k === 'VALOR') classes.push('audit-valor');
                if (c.k === 'DIAS_ATRASO' && Number(l[c.k]) > 0) classes.push('audit-atraso');
                return '<td class="' + classes.join(' ') + '"' + (c.t === 'texto' && txt ? ' title="' + esc(txt) + '"' : '') + '>' + esc(txt) + '</td>';
            }).join('') + '</tr>';
        }).join('') : '<tr><td colspan="' + d.colunas.length + '" class="text-center text-muted p-4">Nenhum lançamento encontrado.</td></tr>';

        var somas = d.colunas.map(function (c) {
            return c.t === 'moeda' ? linhas.reduce(function (s, l) { return s + (Number(l[c.k]) || 0); }, 0) : null;
        });
        var rodape = '<tr>' + d.colunas.map(function (c, i) {
            if (i === 0) return '<td>Total (' + fmtNum.format(linhas.length) + ')</td>';
            return somas[i] === null ? '<td></td>' : '<td class="text-right' + (somas[i] < 0 ? ' text-danger' : '') + '">' + esc(fmtMoeda.format(somas[i])) + '</td>';
        }).join('') + '</tr>';

        $('#audit-thead').html(cab);
        $('#audit-tbody').html(corpo);
        $('#audit-tfoot').html(linhas.length ? rodape : '');

        var filtrado = linhas.length !== d.quantidade;
        $('#audit-qtd').text(fmtNum.format(linhas.length) + (filtrado ? ' de ' + fmtNum.format(d.quantidade) : ''));
        $('#audit-total').text(fmtMoeda.format(total));
        $('#audit-media').text(fmtMoeda.format(linhas.length ? total / linhas.length : 0));
        $('#audit-total-label').text(filtrado ? 'Total filtrado' : 'Total');
        $('#audit-aviso').toggleClass('d-none', !d.truncado).text(d.truncado ? 'Atenção: a lista foi limitada aos primeiros ' + fmtNum.format(d.quantidade) + ' lançamentos; o total pode ser menor que o do card.' : '');
        $('#audit-nota').toggleClass('d-none', !d.nota).text(d.nota || '');
    }

    function abrir(card) {
        var url = $('#dashboard-app').data('detalhe');
        if (!url) return;
        estado = { card: card, dados: null, ordem: null, asc: true, termo: '' };
        $('#audit-busca').val('');
        $('#audit-titulo').text('Auditoria');
        $('#audit-thead,#audit-tbody,#audit-tfoot').empty();
        $('#audit-qtd,#audit-total,#audit-media').text('…');
        $('#audit-aviso,#audit-nota').addClass('d-none');
        $('#audit-loading').removeClass('d-none');
        $('#audit-erro').addClass('d-none');
        $('#dashboardAuditModal').modal('show');

        var filtros = $('#dashboard-filter-form').serialize();
        $.getJSON(url + '?' + filtros + '&card=' + encodeURIComponent(card) + '&cenario=' + encodeURIComponent((card === 'proj_pagar' ? $('#cenario-pagar').val() : $('#cenario-proj').val()) || 'conservador') + '&pedidos=' + ($('.dashboard-prev-check[data-prev="pedidos_pendentes"]').is(':checked') ? '1' : '0')).done(function (r) {
            if (!r || !r.success) {
                $('#audit-erro').removeClass('d-none').text((r && r.message) || 'Não foi possível carregar a auditoria.');
                return;
            }
            estado.dados = r.detalhe;
            $('#audit-titulo').text('Auditoria — ' + r.detalhe.titulo);
            var f = $('#dashboard-filter-form');
            var periodo = (card === 'faturamento' || card === 'ticket_medio' || card === 'pedidos_pendentes' || card === 'clientes_atendidos')
                ? 'Pedidos de ' + data(f.find('[name=data_inicial]').val()) + ' até ' + data(f.find('[name=data_final]').val())
                : 'Vencimento de ' + data(f.find('[name=vencimento_inicial]').val()) + ' até ' + data(f.find('[name=vencimento_final]').val());
            $('#audit-periodo').text(periodo);
            renderizar();
        }).fail(function (x) {
            $('#audit-erro').removeClass('d-none').text((x.responseJSON && x.responseJSON.message) || 'Não foi possível carregar a auditoria.');
        }).always(function () {
            $('#audit-loading').addClass('d-none');
        });
    }

    function exportarCsv() {
        var d = estado.dados;
        if (!d) return;
        var linhas = linhasVisiveis();
        var cel = function (c, v) {
            var t;
            if (v === null || v === undefined || v === '') t = '';
            else if (c.t === 'moeda' || c.t === 'num') t = String(Number(v) || 0).replace('.', ',');
            else if (c.t === 'data') t = data(v);
            else t = String(v);
            return '"' + t.replace(/"/g, '""') + '"';
        };
        var csv = [d.colunas.map(function (c) { return '"' + c.l.replace(/"/g, '""') + '"'; }).join(';')]
            .concat(linhas.map(function (l) { return d.colunas.map(function (c) { return cel(c, l[c.k]); }).join(';'); }))
            .join('\r\n');
        var blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8;' });
        var a = document.createElement('a');
        var hoje = new Date().toISOString().slice(0, 10);
        a.href = URL.createObjectURL(blob);
        a.download = 'auditoria_' + estado.card + '_' + hoje + '.csv';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(function () { URL.revokeObjectURL(a.href); }, 1000);
    }

    $(document).on('click', '.dashboard-audit-btn', function (e) {
        e.preventDefault();
        abrir($(this).data('audit'));
    });
    $(document).on('click', '#audit-thead th', function () {
        var i = Number($(this).data('col'));
        if (estado.ordem === i) estado.asc = !estado.asc; else { estado.ordem = i; estado.asc = true; }
        renderizar();
    });
    $(document).on('input', '#audit-busca', function () {
        estado.termo = $.trim($(this).val()).toLowerCase();
        renderizar();
    });
    $(document).on('click', '#audit-exportar', exportarCsv);
})(jQuery);

/* Trava a rolagem da página enquanto a auditoria está aberta.
   O tema aplica o plugin niceScroll no <html> (scripts.js), que rola a página sozinho ao
   detectar a roda do mouse; por isso ele é desligado durante a auditoria e religado ao fechar. */
jQuery(function ($) {
    var opcoesNice = { styler: 'fb', cursorcolor: '#007AFF', cursorwidth: '6', cursorborderradius: '10px', background: '#F7F7F7', cursorborder: '', zindex: '1000' };
    var religar = false;

    $(document).on('show.bs.modal', '#dashboardAuditModal', function () {
        $('html').addClass('audit-aberto');
        if ($.fn.getNiceScroll && $('html').getNiceScroll().length) {
            $('html').getNiceScroll().remove();
            religar = true;
        }
    });
    $(document).on('hidden.bs.modal', '#dashboardAuditModal', function () {
        $('html').removeClass('audit-aberto');
        if (religar && $.fn.niceScroll) {
            $('html').niceScroll(opcoesNice);
            religar = false;
        }
    });
});
