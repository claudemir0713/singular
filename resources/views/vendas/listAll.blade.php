@extends('layouts.model')
@section('content')
@php
    $f = function ($k, $default = '') use ($dateForm) { return array_key_exists($k, $dateForm) ? $dateForm[$k] : $default; };
    $qtdTotal = 0; $fat1 = 0; $fat2 = 0; $ipi = 0; $frete = 0; $qtdNf = 0; $qtdPd = 0; $valNf = 0; $valPd = 0;
    foreach ($vendas as $v) {
        $liq = $v->FAT_BRUTO + $v->FAT_BRUTO_2 + $v->IPI;
        $qtdTotal += $v->QUANTIDADE; $fat1 += $v->FAT_BRUTO; $fat2 += $v->FAT_BRUTO_2; $ipi += $v->IPI; $frete += $v->FRETE;
        if ($v->TIPO_RECEITA === 'NF') { $qtdNf++; $valNf += $liq; } else { $qtdPd++; $valPd += $liq; }
    }
    $total = $fat1 + $fat2 + $ipi;
    $docs = count($vendas);
    $ticket = $docs ? $total / $docs : 0;
    $m = function ($v, $d = 2) { return number_format($v, $d, ',', '.'); };
    $ufs = ['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO','EX'];
    $filtrosAtivos = collect(['nf','vendedor','cliente','produto','tipo','uf','municipio','forma'])->filter(function ($k) use ($f) { return $f($k) !== '' && $f($k) !== null; })->count();
@endphp
<link href="{{ asset('css/vendas.css') }}?v={{ filemtime(public_path('css/vendas.css')) }}" rel="stylesheet">

<div class="vd-page" id="vendas-app" data-url-itens="{{ route('vendas.itens') }}">
    <div class="vd-header">
        <div>
            <h3 class="vd-title"><i class="fas fa-dollar-sign"></i> Vendas</h3>
            <div class="vd-sub">Notas fiscais de venda e pedidos faturados · {{ date('d/m/Y', strtotime($f('dtI'))) }} a {{ date('d/m/Y', strtotime($f('dtF'))) }}</div>
        </div>
        <div class="vd-header-actions">
            <button type="button" class="vd-btn vd-btn-light" id="vd-export"><i class="fas fa-file-csv"></i> Exportar CSV</button>
            <button type="button" class="vd-btn vd-btn-light" onclick="window.print()"><i class="fas fa-print"></i> Imprimir</button>
        </div>
    </div>

    <form method="get" action="{{ route('vendas.listAll') }}" class="vd-panel vd-filtros" id="vd-form">
        <div class="vd-panel-head">
            <span><i class="fas fa-filter"></i> Filtros @if($filtrosAtivos)<em class="vd-badge">{{ $filtrosAtivos }} ativo(s)</em>@endif</span>
            <span class="vd-atalhos">
                <button type="button" class="vd-chip" data-periodo="hoje">Hoje</button>
                <button type="button" class="vd-chip" data-periodo="semana">7 dias</button>
                <button type="button" class="vd-chip" data-periodo="mes">Este mês</button>
                <button type="button" class="vd-chip" data-periodo="mes_ant">Mês anterior</button>
                <button type="button" class="vd-chip" data-periodo="ano">Este ano</button>
            </span>
        </div>
        <div class="vd-grid">
            <label class="vd-f vd-f-2"><span>De</span><input type="date" id="dtI" name="dtI" value="{{ $f('dtI') }}"></label>
            <label class="vd-f vd-f-2"><span>Até</span><input type="date" id="dtF" name="dtF" value="{{ $f('dtF') }}"></label>
            <label class="vd-f vd-f-2"><span>NF / Pedido</span><input type="text" name="nf" value="{{ $f('nf') }}" placeholder="Número"></label>
            <label class="vd-f vd-f-2">
                <span>Tipo</span>
                <select name="tipo">
                    <option value="">Todos</option>
                    <option value="NF" @selected($f('tipo') === 'NF')>Nota fiscal</option>
                    <option value="PD" @selected($f('tipo') === 'PD')>Pedido faturado</option>
                </select>
            </label>
            <label class="vd-f vd-f-4"><span>Vendedor</span><input type="text" name="vendedor" value="{{ $f('vendedor') }}" list="vd-vendedores" placeholder="Nome do vendedor/representante"></label>
            <label class="vd-f vd-f-4"><span>Cliente</span><input type="text" name="cliente" value="{{ $f('cliente') }}" placeholder="Nome do cliente"></label>
            <label class="vd-f vd-f-4"><span>Produto</span><input type="text" name="produto" value="{{ $f('produto') }}" placeholder="Código ou descrição"></label>
            <label class="vd-f vd-f-4">
                <span>Forma de pagamento</span>
                <select name="forma">
                    <option value="">Todas</option>
                    @foreach($formas as $fp)<option value="{{ $fp }}" @selected(strtoupper($f('forma')) === strtoupper($fp))>{{ $fp }}</option>@endforeach
                </select>
            </label>
            <label class="vd-f vd-f-2">
                <span>UF</span>
                <select name="uf">
                    <option value="">Todas</option>
                    @foreach($ufs as $uf)<option value="{{ $uf }}" @selected(strtoupper($f('uf')) === $uf)>{{ $uf }}</option>@endforeach
                </select>
            </label>
            <label class="vd-f vd-f-3"><span>Município</span><input type="text" name="municipio" value="{{ $f('municipio') }}" placeholder="Cidade"></label>
        </div>
        <datalist id="vd-vendedores">@foreach($vendedores as $vn)<option value="{{ $vn }}">@endforeach</datalist>
        <div class="vd-form-actions">
            <button type="submit" class="vd-btn vd-btn-primary"><i class="fas fa-search"></i> Filtrar</button>
            <a href="{{ route('vendas.listAll') }}" class="vd-btn vd-btn-light"><i class="fas fa-eraser"></i> Limpar</a>
        </div>
    </form>

    <div class="vd-kpis">
        <div class="vd-kpi vd-kpi-main"><small>Faturamento</small><strong>R$ {{ $m($total) }}</strong><span>FAT1 + FAT2 + IPI</span></div>
        <div class="vd-kpi"><small>Documentos</small><strong>{{ $docs }}</strong><span>{{ $qtdNf }} NF · {{ $qtdPd }} pedidos</span></div>
        <div class="vd-kpi"><small>Notas fiscais</small><strong>R$ {{ $m($valNf) }}</strong><span>{{ $total ? $m($valNf / $total * 100, 1) : '0,0' }}% do total</span></div>
        <div class="vd-kpi"><small>Pedidos faturados</small><strong>R$ {{ $m($valPd) }}</strong><span>{{ $total ? $m($valPd / $total * 100, 1) : '0,0' }}% do total</span></div>
        <div class="vd-kpi"><small>Ticket médio</small><strong>R$ {{ $m($ticket) }}</strong><span>por documento</span></div>
        <div class="vd-kpi"><small>Quantidade</small><strong>{{ $m($qtdTotal, 2) }}</strong><span>IPI R$ {{ $m($ipi) }} · Frete R$ {{ $m($frete) }}</span></div>
    </div>

    <div class="vd-panel">
        <div class="vd-panel-head">
            <span><i class="fas fa-list"></i> Documentos <em class="vd-count" id="vd-count">{{ $docs }}</em></span>
            <span class="vd-busca"><i class="fas fa-search"></i><input type="text" id="vd-busca" placeholder="Buscar na lista (nota, cliente, vendedor...)"></span>
        </div>
        <div class="vd-table-wrap">
            <table class="vd-table" id="vd-table">
                <thead>
                    <tr>
                        <th data-sort="text">Documento</th>
                        <th data-sort="text">Tipo</th>
                        <th data-sort="text">Vendedor</th>
                        <th data-sort="text">Cliente</th>
                        <th data-sort="date">Emissão</th>
                        <th data-sort="num" class="r">Qtd</th>
                        <th data-sort="num" class="r">Fat1</th>
                        <th data-sort="num" class="r">Fat2</th>
                        <th data-sort="num" class="r">IPI</th>
                        <th data-sort="num" class="r">Frete</th>
                        <th data-sort="num" class="r">Fat. total</th>
                        <th class="c">Itens</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vendas as $item)
                        @php $liq = $item->FAT_BRUTO + $item->FAT_BRUTO_2 + $item->IPI; $dataIso = date('Y-m-d', strtotime($item->DATA_COMP)); @endphp
                        <tr>
                            <td class="c vd-doc">{{ $item->DOCUMENTO }}</td>
                            <td class="c"><span class="vd-tag {{ $item->TIPO_RECEITA === 'NF' ? 'vd-tag-nf' : 'vd-tag-pd' }}">{{ $item->TIPO_RECEITA }}</span></td>
                            <td>{{ $item->VENDEDOR }}</td>
                            <td>{{ $item->CLIENTE }}</td>
                            <td class="c" data-v="{{ $dataIso }}">{{ date('d/m/Y', strtotime($item->DATA_COMP)) }}</td>
                            <td class="r" data-v="{{ $item->QUANTIDADE }}">{{ $m($item->QUANTIDADE, 2) }}</td>
                            <td class="r" data-v="{{ $item->FAT_BRUTO }}">{{ $m($item->FAT_BRUTO) }}</td>
                            <td class="r" data-v="{{ $item->FAT_BRUTO_2 }}">{{ $m($item->FAT_BRUTO_2) }}</td>
                            <td class="r" data-v="{{ $item->IPI }}">{{ $m($item->IPI) }}</td>
                            <td class="r" data-v="{{ $item->FRETE }}">{{ $m($item->FRETE) }}</td>
                            <td class="r vd-total" data-v="{{ $liq }}">{{ $m($liq) }}</td>
                            <td class="c">
                                <button type="button" class="vd-itens-btn" title="Ver os produtos deste documento"
                                    data-tipo="{{ $item->TIPO_RECEITA }}" data-doc="{{ $item->DOCUMENTO }}" data-cli="{{ $item->COD_CLIENTE }}"
                                    data-data="{{ $dataIso }}" data-cliente="{{ $item->CLIENTE }}" data-vendedor="{{ $item->VENDEDOR }}"
                                    data-total="{{ $liq }}" data-pgto="{{ $item->FORMA_DE_PAGTO }}" data-municipio="{{ $item->MUNICIPIO }}{{ $item->ESTADO ? ' / ' . $item->ESTADO : '' }}">
                                    <i class="fas fa-boxes"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="12" class="vd-vazio"><i class="fas fa-inbox"></i> Nenhuma venda encontrada para os filtros informados.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5">TOTAL</td>
                        <td class="r">{{ $m($qtdTotal, 2) }}</td>
                        <td class="r">{{ $m($fat1) }}</td>
                        <td class="r">{{ $m($fat2) }}</td>
                        <td class="r">{{ $m($ipi) }}</td>
                        <td class="r">{{ $m($frete) }}</td>
                        <td class="r">{{ $m($total) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    {{-- Modal de auditoria dos produtos --}}
    <div class="modal fade" id="vd-modal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content vd-modal">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title"><i class="fas fa-boxes"></i> <span id="vd-m-titulo">Produtos do documento</span></h5>
                        <div class="vd-m-meta" id="vd-m-meta"></div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div id="vd-m-loading" class="vd-m-loading"><i class="fas fa-spinner fa-spin"></i> Carregando produtos...</div>
                    <div id="vd-m-erro" class="vd-m-erro" style="display:none"></div>
                    <div class="vd-m-kpis" id="vd-m-kpis" style="display:none"></div>
                    <div class="vd-table-wrap" id="vd-m-tabela" style="display:none">
                        <table class="vd-table vd-table-itens">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Produto</th>
                                    <th class="c">UN</th>
                                    <th class="r">Qtd</th>
                                    <th class="r">Vlr. unit.</th>
                                    <th class="r">Fat1</th>
                                    <th class="r">Fat2</th>
                                    <th class="r">IPI</th>
                                    <th class="r">Total</th>
                                </tr>
                            </thead>
                            <tbody id="vd-m-itens"></tbody>
                            <tfoot id="vd-m-foot"></tfoot>
                        </table>
                    </div>
                    <div class="vd-m-nota" id="vd-m-nota" style="display:none"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="vd-btn vd-btn-light" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var $app = $('#vendas-app');
    var fmt = function (v, d) { return Number(v || 0).toLocaleString('pt-BR', {minimumFractionDigits: d === undefined ? 2 : d, maximumFractionDigits: d === undefined ? 2 : d}); };
    var pad = function (n) { return (n < 10 ? '0' : '') + n; };
    var iso = function (d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()); };

    // Atalhos de período
    $(document).on('click', '.vd-chip', function () {
        var h = new Date(), i = new Date(h), f = new Date(h);
        switch ($(this).data('periodo')) {
            case 'semana': i.setDate(h.getDate() - 6); break;
            case 'mes': i = new Date(h.getFullYear(), h.getMonth(), 1); break;
            case 'mes_ant': i = new Date(h.getFullYear(), h.getMonth() - 1, 1); f = new Date(h.getFullYear(), h.getMonth(), 0); break;
            case 'ano': i = new Date(h.getFullYear(), 0, 1); break;
        }
        $('#dtI').val(iso(i)); $('#dtF').val(iso(f));
        $('#vd-form').trigger('submit');
    });

    // Busca rápida na lista
    $('#vd-busca').on('input', function () {
        var t = $(this).val().toLowerCase(), n = 0;
        $('#vd-table tbody tr').each(function () {
            var ok = !t || $(this).text().toLowerCase().indexOf(t) >= 0;
            $(this).toggle(ok);
            if (ok && !$(this).find('.vd-vazio').length) n++;
        });
        $('#vd-count').text(n);
    });

    // Ordenação por coluna
    $('#vd-table thead th[data-sort]').on('click', function () {
        var idx = $(this).index(), tipo = $(this).data('sort'), asc = !$(this).hasClass('asc');
        $('#vd-table thead th').removeClass('asc desc');
        $(this).addClass(asc ? 'asc' : 'desc');
        var linhas = $('#vd-table tbody tr').not(':has(.vd-vazio)').get();
        linhas.sort(function (a, b) {
            var ca = $(a).children().eq(idx), cb = $(b).children().eq(idx);
            var va = ca.data('v') !== undefined ? ca.data('v') : ca.text().trim();
            var vb = cb.data('v') !== undefined ? cb.data('v') : cb.text().trim();
            if (tipo === 'num') { va = parseFloat(va) || 0; vb = parseFloat(vb) || 0; }
            else { va = String(va).toLowerCase(); vb = String(vb).toLowerCase(); }
            return (va < vb ? -1 : (va > vb ? 1 : 0)) * (asc ? 1 : -1);
        });
        $.each(linhas, function (_, tr) { $('#vd-table tbody').append(tr); });
    });

    // Exportar CSV (linhas visíveis)
    $('#vd-export').on('click', function () {
        var csv = [];
        var linha = function (cels) { return cels.map(function (c) { return '"' + String(c).replace(/"/g, '""').trim() + '"'; }).join(';'); };
        csv.push(linha($('#vd-table thead th').not(':last').map(function () { return $(this).text(); }).get()));
        $('#vd-table tbody tr:visible').each(function () {
            csv.push(linha($(this).children().not(':last').map(function () { return $(this).text(); }).get()));
        });
        csv.push(linha($('#vd-table tfoot td').not(':last').map(function () { return $(this).text(); }).get()));
        var blob = new Blob(['﻿' + csv.join('\n')], {type: 'text/csv;charset=utf-8;'});
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'vendas_' + $('#dtI').val() + '_a_' + $('#dtF').val() + '.csv';
        document.body.appendChild(a); a.click(); document.body.removeChild(a);
    });

    // Modal de auditoria dos produtos
    var seq = 0;
    $(document).on('click', '.vd-itens-btn', function () {
        var b = $(this), minha = ++seq;
        $('#vd-m-titulo').text((b.data('tipo') === 'NF' ? 'Nota fiscal ' : 'Pedido ') + b.data('doc'));
        var meta = $('#vd-m-meta').empty();
        [['fa-user', b.data('cliente')], ['fa-user-tie', b.data('vendedor')], ['fa-calendar', String(b.data('data')).split('-').reverse().join('/')], ['fa-credit-card', b.data('pgto')], ['fa-map-marker-alt', b.data('municipio')]].forEach(function (x) {
            if (x[1] && String(x[1]).trim() !== '/') meta.append($('<span class="vd-m-chip">').append($('<i class="fas ' + x[0] + '">'), document.createTextNode(' ' + String(x[1]).trim())));
        });
        $('#vd-m-loading').show(); $('#vd-m-erro, #vd-m-kpis, #vd-m-tabela, #vd-m-nota').hide();
        $('#vd-m-itens, #vd-m-foot').empty();
        $('#vd-modal').modal('show');

        $.getJSON($app.data('url-itens'), {tipo: b.data('tipo'), doc: b.data('doc'), cli: b.data('cli'), data: b.data('data')}).done(function (r) {
            if (minha !== seq) { return; }
            $('#vd-m-loading').hide();
            if (!r.success) { $('#vd-m-erro').text(r.message || 'Falha ao carregar os produtos.').show(); return; }
            var itens = r.itens || [], t = {qtd: 0, fat1: 0, fat2: 0, ipi: 0, total: 0};
            if (!itens.length) { $('#vd-m-erro').text('Nenhum produto encontrado para este documento.').show(); return; }
            var tb = $('#vd-m-itens');
            itens.forEach(function (i) {
                t.qtd += i.qtd; t.fat1 += i.fat1; t.fat2 += i.fat2; t.ipi += i.ipi; t.total += i.total;
                tb.append($('<tr>').append(
                    $('<td class="vd-doc">').text(i.codigo),
                    $('<td>').text(i.produto),
                    $('<td class="c">').text(i.un),
                    $('<td class="r">').text(fmt(i.qtd, 2)),
                    $('<td class="r">').text(fmt(i.unitario, 4)),
                    $('<td class="r">').text(fmt(i.fat1)),
                    $('<td class="r">').text(fmt(i.fat2)),
                    $('<td class="r">').text(fmt(i.ipi)),
                    $('<td class="r vd-total">').text(fmt(i.total))
                ));
            });
            $('#vd-m-foot').append($('<tr>').append(
                $('<td colspan="3">').text('TOTAL (' + itens.length + (itens.length === 1 ? ' item)' : ' itens)')),
                $('<td class="r">').text(fmt(t.qtd, 2)), $('<td>'),
                $('<td class="r">').text(fmt(t.fat1)), $('<td class="r">').text(fmt(t.fat2)),
                $('<td class="r">').text(fmt(t.ipi)), $('<td class="r">').text(fmt(t.total))
            ));
            var k = $('#vd-m-kpis').empty();
            [['Itens', itens.length], ['Quantidade', fmt(t.qtd, 2)], ['Produtos (Fat1 + Fat2)', 'R$ ' + fmt(t.fat1 + t.fat2)], ['IPI', 'R$ ' + fmt(t.ipi)], ['Total do documento', 'R$ ' + fmt(t.total)]].forEach(function (x) {
                k.append($('<div class="vd-m-kpi">').append($('<small>').text(x[0]), $('<strong>').text(x[1])));
            });
            k.show(); $('#vd-m-tabela').show();
            var dif = Math.abs(t.total - Number(b.data('total')));
            $('#vd-m-nota').text(dif < 0.05 ? 'Conferido: a soma dos itens fecha com o valor da linha (R$ ' + fmt(b.data('total')) + ').' : 'Atenção: a soma dos itens (R$ ' + fmt(t.total) + ') difere do valor da linha (R$ ' + fmt(b.data('total')) + ').')
                .toggleClass('vd-m-nota-erro', dif >= 0.05).show();
        }).fail(function () {
            if (minha !== seq) { return; }
            $('#vd-m-loading').hide(); $('#vd-m-erro').text('Não foi possível carregar os produtos deste documento.').show();
        });
    });
})();
</script>
@endsection
