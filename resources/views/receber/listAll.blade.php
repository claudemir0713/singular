@extends('layouts.model')
@section('content')

    @php
        $tipoPgtoSelecionados = request()->input('tipo_pgto', []);
        $tipoPgtoSelecionados = is_array($tipoPgtoSelecionados) ? $tipoPgtoSelecionados : [$tipoPgtoSelecionados];

        $tipoPgtoOpcoes = collect($receber)
            ->pluck('TIPO_PGTO')
            ->filter(function ($tipoPgto) {
                return $tipoPgto !== null && trim((string) $tipoPgto) !== '';
            })
            ->map(function ($tipoPgto) {
                return trim((string) $tipoPgto);
            })
            ->unique()
            ->sort()
            ->values();
    @endphp

    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-dollar-sign"></i> Receber
                </h3>
            </td>
        </tr>
    </table>
    <hr>

    <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false"
        aria-controls="collapseExample">
        <span class="fas fa-filter"></span> Filtros
    </button>

    <button class="btn btn-info" type="button" id="btn-imprimirContasReceber">
        <span class="fas fa-print"></span> Imprimir
    </button>

    <p></p>

    {{-- <div class="collapse" id="collapseExample">
        <div class="card card-body">
            <form method="get" action="{{ route('receber.listAll') }}">
                @csrf

                <div class="row">
                    <div class="form-group col-md-2">
                        Doc:
                        <input class="form-control" type="text" id="nf" name="nf" value="{{ array_key_exists('nf', $dateForm) ? $dateForm['nf'] : '' }}">
                    </div>

                    <div class="form-group col-md-4">
                        Cliente:
                        <input class="form-control" type="text" id="cliente" name="cliente" value="{{ array_key_exists('cliente', $dateForm) ? $dateForm['cliente'] : '' }}">
                    </div>

                    <div class="form-group col-md-4">
                        Vendedor:
                        <input class="form-control" type="text" id="vendedor" name="vendedor" value="{{ array_key_exists('vendedor', $dateForm) ? $dateForm['vendedor'] : '' }}">
                    </div>

                    <div class="form-group col-md-2">
                        Tipo:
                        <select class="form-control" id="tipo" name="tipo">
                            <option value="">Todos</option>
                            <option value="NF" {{ ((array_key_exists('tipo', $dateForm) ? $dateForm['tipo'] : '') == 'NF') ? 'selected' : '' }}>NF</option>
                            <option value="PD" {{ ((array_key_exists('tipo', $dateForm) ? $dateForm['tipo'] : '') == 'PD') ? 'selected' : '' }}>CH/PD</option>
                            <option value="CH" {{ ((array_key_exists('tipo', $dateForm) ? $dateForm['tipo'] : '') == 'CH') ? 'selected' : '' }}>CHEQUES</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="form-group col-md-2">
                        Emissão de:
                        <input class="form-control" type="date" id="dtIEmissa" name="dtIEmissa" value="{{ array_key_exists('dtIEmissa', $dateForm) ? $dateForm['dtIEmissa'] : '' }}">
                    </div>

                    <div class="form-group col-md-2">
                        Emissão até:
                        <input class="form-control" type="date" id="dtFEmissa" name="dtFEmissa" value="{{ array_key_exists('dtFEmissa', $dateForm) ? $dateForm['dtFEmissa'] : '' }}">
                    </div>

                    <div class="form-group col-md-2">
                        Vencimento de:
                        <input class="form-control" type="date" id="dtI" name="dtI" value="{{ array_key_exists('dtI', $dateForm) ? $dateForm['dtI'] : date('Y-m-d') }}">
                    </div>

                    <div class="form-group col-md-2">
                        Vencimento até:
                        <input class="form-control" type="date" id="dtF" name="dtF" value="{{ array_key_exists('dtF', $dateForm) ? $dateForm['dtF'] : date('Y-m-d') }}">
                    </div>

                    <div class="form-group col-md-4">
                        Tipo de pagamento:
                        <select class="form-control tipo-pgto-multiselect" id="tipo_pgto" name="tipo_pgto[]" multiple size="4">
                            @foreach ($tipoPgtoOpcoes as $tipoPgtoOpcao)
                                <option value="{{ $tipoPgtoOpcao }}" {{ in_array($tipoPgtoOpcao, $tipoPgtoSelecionados) ? 'selected' : '' }}>
                                    {{ $tipoPgtoOpcao }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Use Ctrl para selecionar ou remover mais de uma opção.</small>
                    </div>
                </div>

                <button class="btn btn-primary" type="submit">
                    <span class="fas fa-play"></span> Filtrar
                </button>
            </form>
        </div>
    </div> --}}

    <div class="collapse" id="collapseExample">
        <div class="card card-body">
            <form method="get" action="{{ route('receber.listAll') }}">
            @csrf

            <div class="row">
                <div class="form-group col-md-2">
                    Doc:
                    <input class="form-control" type="text" id="nf" name="nf"
                        value="{{ array_key_exists('nf', $dateForm) ? $dateForm['nf'] : '' }}">
                </div>

                <div class="form-group col-md-4">
                    Cliente:
                    <input class="form-control" type="text" id="cliente" name="cliente"
                        value="{{ array_key_exists('cliente', $dateForm) ? $dateForm['cliente'] : '' }}">
                </div>

                <div class="form-group col-md-4">
                    Vendedor:
                    <input class="form-control" type="text" id="vendedor" name="vendedor"
                        value="{{ array_key_exists('vendedor', $dateForm) ? $dateForm['vendedor'] : '' }}">
                </div>

                <div class="form-group col-md-2">
                    Tipo:
                    <select class="form-control" id="tipo" name="tipo">
                        <option value="">Todos</option>

                        <option value="NF"
                            {{ (array_key_exists('tipo', $dateForm) ? $dateForm['tipo'] : '') == 'NF' ? 'selected' : '' }}>
                            NF
                        </option>

                        <option value="PD"
                            {{ (array_key_exists('tipo', $dateForm) ? $dateForm['tipo'] : '') == 'PD' ? 'selected' : '' }}>
                            CH/PD
                        </option>

                        <option value="CH"
                            {{ (array_key_exists('tipo', $dateForm) ? $dateForm['tipo'] : '') == 'CH' ? 'selected' : '' }}>
                            CHEQUES
                        </option>
                    </select>
                </div>
            </div>

            <div class="row">
                <div class="form-group col-md-2">
                    Emissão de:
                    <input class="form-control" type="date" id="dtIEmissa" name="dtIEmissa"
                        value="{{ array_key_exists('dtIEmissa', $dateForm) ? $dateForm['dtIEmissa'] : '' }}">
                </div>

                <div class="form-group col-md-2">
                    Emissão até:
                    <input class="form-control" type="date" id="dtFEmissa" name="dtFEmissa"
                        value="{{ array_key_exists('dtFEmissa', $dateForm) ? $dateForm['dtFEmissa'] : '' }}">
                </div>

                <div class="form-group col-md-2">
                    Vencimento de:
                    <input class="form-control" type="date" id="dtI" name="dtI"
                        value="{{ array_key_exists('dtI', $dateForm) ? $dateForm['dtI'] : date('Y-m-d') }}">
                </div>

                <div class="form-group col-md-2">
                    Vencimento até:
                    <input class="form-control" type="date" id="dtF" name="dtF"
                        value="{{ array_key_exists('dtF', $dateForm) ? $dateForm['dtF'] : date('Y-m-d') }}">
                </div>

                <div class="form-group col-md-4">
                    Tipo de pagamento:

                    <select class="form-control tipo-pgto-multiselect" id="tipo_pgto" name="tipo_pgto[]" multiple
                        size="4">
                        @foreach ($tiposPgto as $tipoPgto)
                            @php
                                $descricaoTipoPgto = strtoupper(trim((string) $tipoPgto->CON_TIPO_DESC));
                            @endphp

                            <option value="{{ $descricaoTipoPgto }}"
                                {{ in_array($descricaoTipoPgto, $tipoPgtoSelecionados, true) ? 'selected' : '' }}>
                                {{ $tipoPgto->CON_TIPO_DESC }}
                            </option>
                        @endforeach

                        @php
                            $possuiCheque = $tiposPgto->contains(function ($tipoPgto) {
                                return strtoupper(trim((string) $tipoPgto->CON_TIPO_DESC)) === 'CHEQUE';
                            });
                        @endphp

                        @if (!$possuiCheque)
                            <option value="CHEQUE" {{ in_array('CHEQUE', $tipoPgtoSelecionados, true) ? 'selected' : '' }}>
                                CHEQUE
                            </option>
                        @endif
                    </select>

                    <small class="form-text text-muted">
                        Segure Ctrl para selecionar ou remover mais de uma opção.
                    </small>
                </div>
            </div>

            <button class="btn btn-primary" type="submit">
                <span class="fas fa-play"></span> Filtrar
            </button>
            </form>
        </div>
    </div>

    <p></p>

    <style>
        .fonte-10 {
            font-size: 10px;
        }

        .courier {
            font-family: 'Courier New', Courier, monospace;
        }

        /* Cabeçalhos diferenciados */
        .bg-header-rep {
            background: #d3d3d3;
        }

        .bg-header-ori {
            background: #f1f1f1;
        }

        .subtotal-origem {
            background: #efefef;
        }

        .subtotal-rep {
            background: #e3e3e3;
        }

        /* Destaques de vencimento */
        .vencido {
            color: #b30000;
            font-weight: bold;
        }

        .vencido-row {
            background: #ffe5e5;
        }

        .vencido-row td {
            border-top: 1px solid #f1bcbc;
        }

        .badge-vencido {
            display: inline-block;
            padding: 0.1rem 0.35rem;
            font-size: 9px;
            border-radius: 3px;
            background: #b30000;
            color: #fff;
            margin-left: 6px;
        }

        .saldo-vencido {
            background: #ffecec;
            color: #b30000;
            font-weight: bold;
        }

        /* Accordion com ícones e animação */
        .acc-toggle {
            cursor: pointer;
            user-select: none;
        }

        .acc-title {
            display: flex;
            align-items: center;
            gap: .5rem;
        }

        .acc-icon {
            display: inline-block;
            transition: transform .2s ease;
            margin-right: 6px;
            color: #333;
        }

        .acc-toggle[aria-expanded="true"] .acc-icon {
            transform: rotate(90deg);
        }

        .sum-col-right {
            text-align: right;
            white-space: nowrap;
        }

        /* Correções para alinhamento das colunas */
        .table {
            table-layout: fixed;
        }

        .table th,
        .table td {
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Corrige o display do collapse em linhas/tbody de tabela */
        table tr.collapse {
            display: none;
        }

        table tr.collapse.show {
            display: table-row !important;
        }

        table tbody.collapse {
            display: none;
        }

        table tbody.collapse.show {
            display: table-row-group !important;
        }

        /* Quebra suave do nome do cliente */
        .td-cliente {
            word-break: break-word;
            white-space: normal;
        }

        /* Tipo de pagamento */
        .td-tipo-pgto {
            white-space: nowrap;
        }

        .tipo-pgto-multiselect {
            min-height: 96px;
        }

        /* Impressão: manter tudo expandido e cores suaves */
        @media print {
            table tr.collapse {
                display: table-row !important;
            }

            table tbody.collapse {
                display: table-row-group !important;
            }

            .acc-icon {
                transform: rotate(90deg) !important;
            }

            .bg-header-ori {
                background: #f6f6f6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .bg-header-rep {
                background: #dcdcdc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>

    @php
        use Illuminate\Support\Str;

        // Garante Collection
        $receberCol = $receber instanceof \Illuminate\Support\Collection ? $receber : collect($receber);

        // Aplica o multiselect de TIPO_PGTO na coleção exibida
        if (!empty($tipoPgtoSelecionados)) {
            $receberCol = $receberCol
                ->filter(function ($item) use ($tipoPgtoSelecionados) {
                    return in_array(trim((string) $item->TIPO_PGTO), $tipoPgtoSelecionados);
                })
                ->values();
        }

        /** Normaliza ORIGEM para agrupamento consistente */
        $normalizeOrigem = function ($origemRaw) {
            $s = strtoupper(trim((string) $origemRaw));
            $s = Str::ascii($s);
            $s = preg_replace('/[^\w]+/u', ' ', $s);
            $s = preg_replace('/\s+/', ' ', $s);
            $s = trim($s);

            // DUPLICATA
            if (strpos($s, 'DUP') !== false || strpos($s, 'DPL') !== false) {
                return 'DUPLICATA';
            }

            // CHEQUE e variantes
            $isCheque =
                strpos($s, 'CHEQ') !== false ||
                preg_match('/\bCH\b/u', $s) ||
                strpos($s, 'CH PD') !== false ||
                strpos($s, 'CH/PD') !== false ||
                preg_match('/\bPD\b/u', $s) ||
                strpos($s, 'PRE DAT') !== false ||
                strpos($s, 'PREDAT') !== false ||
                strpos($s, 'PRE DATADO') !== false;

            if ($isCheque) {
                return 'CHEQUE';
            }

            return $s;
        };

        // Ordenação das origens
        $origemOrder = [
            'DUPLICATA' => 1,
            'CHEQUE' => 2,
        ];

        // Totais gerais
        $grand = ['original' => 0, 'pago' => 0, 'juros' => 0, 'multa' => 0, 'saldo' => 0];

        // Helpers
        $fmt = fn($v) => number_format((float) $v, 2, ',', '.');

        // Vencido = vencimento < hoje
        $isVencido = function ($dataVenc) {
            try {
                $v = \Carbon\Carbon::parse($dataVenc)->startOfDay();
                return $v->lt(now()->startOfDay());
            } catch (\Throwable $e) {
                return false;
            }
        };

        // Slug para classes CSS
        $slug = function ($value) {
            $s = strtolower(Str::ascii((string) $value));
            $s = preg_replace('/[^\w]+/u', '-', $s);
            return trim($s, '-');
        };
    @endphp

    <table class="table table-bordered table-condensed fonte-10 courier">
        {{-- Garante larguras idênticas em todas as linhas --}}
        <colgroup>
            <col style="width: 9%;"> {{-- NF --}}
            <col style="width: 4%;"> {{-- PARCELA --}}
            <col style="width: 19%;"> {{-- CLIENTE --}}
            <col style="width: 10%;"> {{-- TIPO PGTO --}}
            <col style="width: 8%;"> {{-- EMISSÃO --}}
            <col style="width: 10%;"> {{-- VENCIMENTO --}}
            <col style="width: 9%;"> {{-- VALOR --}}
            <col style="width: 8%;"> {{-- PAGO --}}
            <col style="width: 6%;"> {{-- MULTA --}}
            <col style="width: 6%;"> {{-- JUROS --}}
            <col style="width: 11%;"> {{-- SALDO --}}
        </colgroup>

        <thead>
            <tr>
                <th>NF</th>
                <th>PARCELA</th>
                <th>CLIENTE</th>
                <th>EMISSÃO</th>
                <th>VENCIMENTO</th>
                <th>VALOR</th>
                <th>PAGO</th>
                <th>MULTA</th>
                <th>JUROS</th>
                <th>SALDO</th>
                <th>COBRANÇA</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($receberCol->groupBy('COD_REP')->sortKeys() as $codRep => $itensRep)
                @php
                    $repNome = optional($itensRep->first())->REPRESENTANTE;

                    // Totais do REPRESENTANTE
                    $repSums = [
                        'original' => $itensRep->sum('CON_VALOR_ORIGINAL'),
                        'pago' => $itensRep->sum('CON_VALOR_TOTAL_PAGO'),
                        'juros' => $itensRep->sum('CON_VALOR_JUROS'),
                        'multa' => $itensRep->sum('CON_VALOR_MULTA'),
                        'saldo' => $itensRep->sum('CON_VALOR_CORRIGIDO'),
                    ];
                    foreach ($repSums as $k => $v) {
                        $grand[$k] += $v;
                    }

                    $repKey = 'rep-' . $slug($codRep);
                @endphp

                {{-- Cabeçalho do REPRESENTANTE --}}
                <tr class="bg-header-rep acc-toggle" data-toggle="collapse" data-target=".{{ $repKey }}"
                    aria-expanded="true" aria-controls="{{ $repKey }}">
                    <td colspan="5">
                        <span class="acc-title">
                            <i class="fas fa-chevron-right acc-icon"></i>
                            <i class="fas fa-user-tie" style="opacity:.85;"></i>
                            <b>{{ $codRep }} - {{ $repNome }}</b>
                        </span>
                    </td>
                    <td class="sum-col-right"><b>{{ $fmt($repSums['original']) }}</b></td>
                    <td class="sum-col-right"><b>{{ $fmt($repSums['pago']) }}</b></td>
                    <td class="sum-col-right"><b>{{ $fmt($repSums['multa']) }}</b></td>
                    <td class="sum-col-right"><b>{{ $fmt($repSums['juros']) }}</b></td>
                    <td class="sum-col-right"><b>{{ $fmt($repSums['saldo']) }}</b></td>
                    <td></td>
                </tr>

                {{-- AGRUPAMENTO POR ORIGEM --}}
                @foreach ($itensRep->groupBy(fn($row) => $normalizeOrigem($row->ORIGEM))->sortBy(function ($grupo, $origemNorm) use ($origemOrder) {
            $rank = $origemOrder[$origemNorm] ?? 999;
            return sprintf('%03d-%s', $rank, $origemNorm);
        }) as $origemNorm => $itensOrigem)
                    @php
                        $ori = [
                            'original' => $itensOrigem->sum('CON_VALOR_ORIGINAL'),
                            'pago' => $itensOrigem->sum('CON_VALOR_TOTAL_PAGO'),
                            'juros' => $itensOrigem->sum('CON_VALOR_JUROS'),
                            'multa' => $itensOrigem->sum('CON_VALOR_MULTA'),
                            'saldo' => $itensOrigem->sum('CON_VALOR_CORRIGIDO'),
                        ];

                        $origKey = 'orig-' . $repKey . '-' . $slug($origemNorm);

                        $origIcon = 'fas fa-layer-group';
                        if ($origemNorm === 'DUPLICATA') {
                            $origIcon = 'fas fa-file-invoice';
                        } elseif ($origemNorm === 'CHEQUE') {
                            $origIcon = 'fas fa-money-check-alt';
                        }
                    @endphp

                    {{-- Cabeçalho da ORIGEM --}}
                    <tr class="bg-header-ori acc-toggle collapse show {{ $repKey }}" data-toggle="collapse"
                        data-target=".{{ $origKey }}" aria-expanded="true" aria-controls="{{ $origKey }}">
                        <td colspan="5">
                            <span class="acc-title">
                                <i class="fas fa-chevron-right acc-icon"></i>
                                <i class="{{ $origIcon }}" style="opacity:.9;"></i>
                                <b>{{ $origemNorm }}</b>
                            </span>
                        </td>
                        <td class="sum-col-right"><b>{{ $fmt($ori['original']) }}</b></td>
                        <td class="sum-col-right"><b>{{ $fmt($ori['pago']) }}</b></td>
                        <td class="sum-col-right"><b>{{ $fmt($ori['multa']) }}</b></td>
                        <td class="sum-col-right"><b>{{ $fmt($ori['juros']) }}</b></td>
                        <td class="sum-col-right"><b>{{ $fmt($ori['saldo']) }}</b></td>
                        <td></td>
                    </tr>

                    {{-- Linhas de dados --}}
                    @foreach ($itensOrigem->sortBy('CON_DT_VENCIMENTO') as $item)
                        @php
                            $isPastDue = $isVencido($item->CON_DT_VENCIMENTO);
                            $vencClass = $isPastDue ? 'vencido' : '';
                            $diasAtraso = $isPastDue
                                ? \Carbon\Carbon::parse($item->CON_DT_VENCIMENTO)
                                    ->startOfDay()
                                    ->diffInDays(now()->startOfDay())
                                : null;
                        @endphp

                        <tr
                            class="collapse show {{ $repKey }} {{ $origKey }} {{ $isPastDue ? 'vencido-row' : '' }}">
                            <td>{{ $item->CON_NUMERO }}</td>
                            <td align="center">{{ $item->CON_SEQUENCIA }}</td>
                            <td class="td-cliente">{{ $item->CLIENTE }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->CON_DT_INCLUSAO)->format('d/m/Y') }}</td>
                            <td class="{{ $vencClass }}" align="center">
                                {{ \Carbon\Carbon::parse($item->CON_DT_VENCIMENTO)->format('d/m/Y') }}
                                @if ($diasAtraso !== null)
                                    <div class="badge-vencido"><sup>{{ $diasAtraso }}d</sup></div>
                                @endif
                            </td>
                            <td align="right">{{ $fmt($item->CON_VALOR_ORIGINAL) }}</td>
                            <td align="right">{{ $fmt($item->CON_VALOR_TOTAL_PAGO) }}</td>
                            <td align="right">{{ $fmt($item->CON_VALOR_MULTA) }}</td>
                            <td align="right">{{ $fmt($item->CON_VALOR_JUROS) }}</td>
                            <td align="right" class="{{ $isPastDue ? 'saldo-vencido' : '' }}">
                                {{ $fmt($item->CON_VALOR_CORRIGIDO) }}
                            </td>
                            <td class="td-tipo-pgto">{{ $item->TIPO_PGTO }}</td>
                        </tr>
                    @endforeach
                @endforeach
            @empty
                <tr>
                    <td colspan="11" class="text-center">Nenhum registro encontrado.</td>
                </tr>
            @endforelse
        </tbody>

        <tfoot>
            <tr>
                <td colspan="5"><b>TOTAL GERAL</b></td>
                <td align="right"><b>{{ $fmt($grand['original']) }}</b></td>
                <td align="right"><b>{{ $fmt($grand['pago']) }}</b></td>
                <td align="right"><b>{{ $fmt($grand['multa']) }}</b></td>
                <td align="right"><b>{{ $fmt($grand['juros']) }}</b></td>
                <td align="right"><b>{{ $fmt($grand['saldo']) }}</b></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Atualiza aria-expanded com base no estado das linhas alvo
            function refreshAriaExpanded(row) {
                var targetSel = row.getAttribute('data-target');
                if (!targetSel) return;

                var targets = document.querySelectorAll(targetSel);
                if (!targets.length) return;

                var expanded = Array.prototype.some.call(targets, function(el) {
                    return el.classList.contains('show');
                });

                row.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            }

            // Inicializa o estado
            document.querySelectorAll('.acc-toggle').forEach(function(row) {
                refreshAriaExpanded(row);
            });

            // Ouve eventos do Bootstrap Collapse
            document.querySelectorAll('[data-toggle="collapse"]').forEach(function(trigger) {
                var targetSel = trigger.getAttribute('data-target');
                if (!targetSel) return;

                var targets = document.querySelectorAll(targetSel);

                targets.forEach(function(el) {
                    el.addEventListener('shown.bs.collapse', function() {
                        refreshAriaExpanded(trigger);
                    });

                    el.addEventListener('hidden.bs.collapse', function() {
                        refreshAriaExpanded(trigger);
                    });
                });

                // Evita seleção de texto e deixa o Bootstrap fazer o toggle
                trigger.addEventListener('click', function(ev) {
                    ev.preventDefault();
                });
            });
        });
    </script>
@endsection
