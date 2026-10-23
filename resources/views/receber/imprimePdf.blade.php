{{-- ===== Cabeçalho (opcional) ===== --}}
<div id="report-header">
    <div class="report-title">Contas a Receber</div>
    <div class="report-meta">
        {{-- Ex: Período e filtros dinâmicos --}}
        @if(!empty($dateForm ?? null))
            <span>Período:
                {{ ($dateForm['dtI'] ?? null) ? \Carbon\Carbon::parse($dateForm['dtI'])->format('d/m/Y') : '__/__/____' }}
                —
                {{ ($dateForm['dtF'] ?? null) ? \Carbon\Carbon::parse($dateForm['dtF'])->format('d/m/Y') : '__/__/____' }}
            </span>
            @if(!empty($dateForm['cliente'])) <span> • Cliente: {{ $dateForm['cliente'] }}</span>@endif
            @if(!empty($dateForm['vendedor'])) <span> • Vendedor: {{ $dateForm['vendedor'] }}</span>@endif
        @endif
        <span class="when"> • Gerado em: {{ now()->format('d/m/Y H:i') }}</span>
    </div>
</div>

<style>
    /* ===== Base (compatível com Dompdf) ===== */
    html, body {
        font-family: "DejaVu Sans", sans-serif;
        color: #333;
        font-size: 11px;
        line-height: 1.35;
    }

    /* Repetição de cabeçalho/rodapé da tabela em cada página (Dompdf) */
    thead { display: table-header-group; }
    tfoot { display: table-footer-group; }

    /* ===== Cabeçalho do relatório ===== */
    #report-header {
        margin-bottom: 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e0e0e0;
    }
    .report-title {
        font-size: 18px;
        font-weight: 700;
        color: #222;
        letter-spacing: .2px;
        margin-bottom: 3px;
    }
    .report-meta {
        font-size: 10px;
        color: #666;
    }

    /* ===== Densidades ===== */
    .density-compact th, .density-compact td { padding: 4px 5px; }
    .density-comfort th, .density-comfort td { padding: 7px 8px; }

    /* ===== Tabela ===== */
    table {
        width: 100%;
        border-collapse: separate;   /* <- evita bug do dompdf */
        border-spacing: 0;           /* <- visual de “collapse” */
        table-layout: fixed;
        font-size: 10.8px;
    }

    th {
        text-align: center;
        background: #f7f7f7;
        color: #444;
        font-weight: 700;
        white-space: nowrap;
        border-bottom: 1px solid #e6e6e6; /* borda no TH (não no TR) */
    }

    td {
        vertical-align: middle;
        border-bottom: 1px solid #f1f1f1; /* borda nas células (não no TR) */
    }

    /* Zebra rows — suave (ok no dompdf) */
    tbody tr.row-item:nth-child(odd) { background: #fcfcfc; }

    /* Larguras */
    .col-nf      { width: 8%;  }
    .col-parc    { width: 5%;  }
    .col-cliente { width: 24%; }
    .col-tipo    { width: 10%; }
    .col-emis    { width: 8%;  }
    .col-venc    { width: 9%;  }
    .col-valor   { width: 9%;  }
    .col-pago    { width: 8%;  }
    .col-multa   { width: 5%;  }
    .col-juros   { width: 5%;  }
    .col-saldo   { width: 9%;  }

    .nowrap   { white-space: nowrap; }
    .num      { text-align: right; white-space: nowrap; font-size: 10px; }
    .cliente  { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .tipo-pgto { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 9px; }

    /* ===== Hierarquia: Representante / Origem ===== */
    /* Nada de borda em TR! Tudo aplicado em TD/TH */

    .header-rep { background: #f0f0f0; font-size: 11.5px; font-weight: 700; color: #333; }
    .header-rep td {
        padding: 7px 6px;
        border-top: 1px solid #e6e6e6;       /* moveu do TR para TD */
    }
    .header-rep td:first-child {
        border-left: 4px solid #888888;      /* moveu do TR para o primeiro TD */
    }

    .header-origem { background: #fafafa; }
    .header-origem td {
        padding: 6px;
        font-weight: 700;
        color: #444;
        border-top: 1px solid #ededed;       /* moveu do TR para TD */
    }
    .header-origem td:first-child {
        border-left: 3px solid #b5b5b5;      /* moveu do TR para o primeiro TD */
    }

    .subtotal-rep td {
        background: #f3f3f3;
        font-weight: 700;
        border-top: 1px solid #dddddd;       /* moveu do TR para TD */
    }

    /* Separador entre representantes */
    .sep-entre-reps td {
        border: 0;
        height: 8px;
        padding: 0;
        background: transparent;
    }

    /* ===== Vencidos ===== */
    .vencido { color: #cc0000; font-weight: 600; }
    .badge-atraso {
        display: inline-block;
        background: #ffe7e7;
        color: #c00000;
        padding: 1px 4px;
        border-radius: 3px;
        font-size: 8.5px;
        margin-left: 4px;
        vertical-align: baseline;
        white-space: nowrap;
    }

    /* Evita quebra no meio de linhas importantes (sem mexer em bordas do TR) */
    .row-item, .header-rep, .header-origem { page-break-inside: avoid; }
</style>

@php
    use Illuminate\Support\Str;

    // Collection
    $receberCol = $receber instanceof \Illuminate\Support\Collection ? $receber : collect($receber);

    // Ordenação base
    $receberCol = $receberCol
        ->sortBy('CON_DT_VENCIMENTO')
        ->sortBy('ORIGEM')
        ->sortBy('COD_REP');

    // Totais gerais
    $grand = ['original' => 0, 'pago' => 0, 'juros' => 0, 'multa' => 0, 'saldo' => 0];

    // Formatação
    $fmt = fn($v) => number_format((float)$v, 2, ',', '.');

    // Normalização de origem
    $normalizeOrigem = function($origemRaw) {
        $s = strtoupper(trim((string)$origemRaw));
        $s = Str::ascii($s);
        $s = preg_replace('/[^\w]+/u', ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        $s = trim($s);

        if (strpos($s, 'DUP') !== false || strpos($s, 'DPL') !== false) return 'DUPLICATA';

        $isCheque =
            strpos($s, 'CHEQ') !== false ||
            preg_match('/\bCH\b/u', $s) ||
            strpos($s, 'CH PD') !== false ||
            strpos($s, 'CHPD') !== false ||
            preg_match('/\bPD\b/u', $s) ||
            strpos($s, 'PRE DAT') !== false ||
            strpos($s, 'PREDAT') !== false ||
            strpos($s, 'PRE DATADO') !== false;

        if ($isCheque) return 'CHEQUE';
        return $s;
    };

    $origemOrder = ['DUPLICATA' => 1, 'CHEQUE' => 2];

    // Vencido (mude para ->lte() se quiser incluir hoje)
    $isVencido = function($dataVenc) {
        try {
            $v = \Carbon\Carbon::parse($dataVenc)->startOfDay();
            return $v->lt(now()->startOfDay());
        } catch (\Throwable $e) { return false; }
    };
@endphp

{{-- Use "density-compact" ou "density-comfort" abaixo --}}
<div class="density-compact">
    <table>
        <thead>
            <tr>
                <th class="col-nf">NF</th>
                <th class="col-parc">PARCELA</th>
                <th class="col-cliente">CLIENTE</th>
                <th class="col-tipo">TIPO PGTO</th>
                <th class="col-emis">EMISSÃO</th>
                <th class="col-venc">VENCIMENTO</th>
                <th class="col-valor">VALOR</th>
                <th class="col-pago">PAGO</th>
                <th class="col-multa">MULTA</th>
                <th class="col-juros">JUROS</th>
                <th class="col-saldo">SALDO</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($receberCol->groupBy('COD_REP')->sortKeys() as $codRep => $itensRep)
                @php
                    $repNome = optional($itensRep->first())->REPRESENTANTE;
                    $rep = ['original'=>0,'pago'=>0,'juros'=>0,'multa'=>0,'saldo'=>0];
                @endphp

                {{-- Representante (borda aplicada nos TDs) --}}
                <tr class="header-rep">
                    <td colspan="11"><b>{{ $codRep }} - {{ $repNome }}</b></td>
                </tr>

                @foreach (
                    $itensRep
                        ->groupBy(fn($row) => $normalizeOrigem($row->ORIGEM))
                        ->sortBy(function($grupo, $origemNorm) use ($origemOrder) {
                            $rank = $origemOrder[$origemNorm] ?? 999;
                            return sprintf('%03d-%s', $rank, $origemNorm);
                        })
                    as $origemNorm => $itensOrigem
                )
                    @php
                        $ori = [
                            'original' => $itensOrigem->sum('CON_VALOR_ORIGINAL'),
                            'pago'     => $itensOrigem->sum('CON_VALOR_TOTAL_PAGO'),
                            'juros'    => $itensOrigem->sum('CON_VALOR_JUROS'),
                            'multa'    => $itensOrigem->sum('CON_VALOR_MULTA'),
                            'saldo'    => $itensOrigem->sum('CON_VALOR_CORRIGIDO'),
                        ];
                        foreach ($ori as $k => $v) { $rep[$k] += $v; }
                    @endphp

                    {{-- Origem (borda aplicada nos TDs) --}}
                    <tr class="header-origem">
                        <td colspan="6"><b>{{ $origemNorm }}</b></td>
                        <td class="num"><b>{{ $fmt($ori['original']) }}</b></td>
                        <td class="num"><b>{{ $fmt($ori['pago']) }}</b></td>
                        <td class="num"><b>{{ $fmt($ori['multa']) }}</b></td>
                        <td class="num"><b>{{ $fmt($ori['juros']) }}</b></td>
                        <td class="num"><b>{{ $fmt($ori['saldo']) }}</b></td>
                    </tr>

                    {{-- Linhas --}}
                    @foreach ($itensOrigem->sortBy('CON_DT_VENCIMENTO') as $item)
                        @php
                            $pastDue = $isVencido($item->CON_DT_VENCIMENTO);
                            $vencClass = $pastDue ? 'vencido' : '';
                            $diasAtraso = $pastDue
                                ? \Carbon\Carbon::parse($item->CON_DT_VENCIMENTO)->startOfDay()->diffInDays(now()->startOfDay())
                                : null;
                        @endphp
                        <tr class="row-item">
                            <td class="nowrap">{{ $item->CON_NUMERO }}</td>
                            <td class="nowrap" style="text-align:center">{{ $item->CON_SEQUENCIA }}</td>
                            <td class="cliente">{{ $item->CLIENTE }}</td>
                            <td class="tipo-pgto">{{ $item->TIPO_PGTO }}</td>
                            <td class="nowrap">{{ \Carbon\Carbon::parse($item->CON_DT_INCLUSAO)->format('d/m/Y') }}</td>
                            <td class="nowrap {{ $vencClass }}">
                                {{ \Carbon\Carbon::parse($item->CON_DT_VENCIMENTO)->format('d/m/Y') }}
                                @if ($diasAtraso !== null)
                                    <span class="badge-atraso">{{ $diasAtraso }}d</span>
                                @endif
                            </td>
                            <td class="num">{{ $fmt($item->CON_VALOR_ORIGINAL) }}</td>
                            <td class="num">{{ $fmt($item->CON_VALOR_TOTAL_PAGO) }}</td>
                            <td class="num">{{ $fmt($item->CON_VALOR_MULTA) }}</td>
                            <td class="num">{{ $fmt($item->CON_VALOR_JUROS) }}</td>
                            <td class="num">{{ $fmt($item->CON_VALOR_CORRIGIDO) }}</td>
                        </tr>
                    @endforeach
                @endforeach

                {{-- Total do Representante (borda aplicada nos TDs) --}}
                <tr class="subtotal-rep">
                    <td colspan="6"><b>Total do Representante</b></td>
                    <td class="num"><b>{{ $fmt($rep['original']) }}</b></td>
                    <td class="num"><b>{{ $fmt($rep['pago']) }}</b></td>
                    <td class="num"><b>{{ $fmt($rep['multa']) }}</b></td>
                    <td class="num"><b>{{ $fmt($rep['juros']) }}</b></td>
                    <td class="num"><b>{{ $fmt($rep['saldo']) }}</b></td>
                </tr>

                @php foreach ($rep as $k => $v) { $grand[$k] += $v; } @endphp

                {{-- Separador entre representantes (apenas se houver próximo) --}}
                @if (!$loop->last)
                    <tr class="sep-entre-reps">
                        <td colspan="11"></td>
                    </tr>
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6"><b>TOTAL GERAL</b></td>
                <td class="num"><b>{{ $fmt($grand['original']) }}</b></td>
                <td class="num"><b>{{ $fmt($grand['pago']) }}</b></td>
                <td class="num"><b>{{ $fmt($grand['multa']) }}</b></td>
                <td class="num"><b>{{ $fmt($grand['juros']) }}</b></td>
                <td class="num"><b>{{ $fmt($grand['saldo']) }}</b></td>
            </tr>
        </tfoot>
    </table>
</div>
