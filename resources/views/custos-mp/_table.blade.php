@php
    $totalRegistros = $custos->count();
    $totalUtilizadas = $custos->where('utilizada_estrutura', 'S')->count();
    $totalNaoUtilizadas = $custos->where('utilizada_estrutura', 'N')->count();
@endphp

<table class="table table-bordered table-condensed fonte-10 courier">
    <thead>
        <tr>
            <th width="7%">Código</th>
            <th width="23%">Descrição</th>
            <th width="5%">Unid.</th>
            <th width="5%">Tipo</th>
            <th width="8%" class="text-right">Custo Sistema</th>
            <th width="8%" class="text-right">Custo Manual</th>
            <th width="8%" class="text-right">Dif</th>
            <th width="8%" class="text-right">Crédito Imp. %</th>
            <th width="8%" class="text-right">Valor Unit.</th>
            <th width="8%" class="text-right">Custo Líquido</th>
            <th width="6%">Utilizada</th>
            <th width="6%">Origem</th>
            <th width="4%">Ação</th>
            <th width="4%">Status</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($custos as $item)
            <tr
                data-ano="{{ $ano }}"
                data-mes="{{ $mes }}"
                data-cod="{{ $item->cod_mp }}"
            >
                <td>
                    <strong>{{ $item->cod_mp }}</strong>
                </td>

                <td>
                    {{ $item->desc_mp }}
                </td>

                <td align="center">
                    <input
                        type="text"
                        class="form-control form-control-sm text-center unid_med fonte-10"
                        value="{{ $item->unid_med ?? '' }}"
                        maxlength="20"
                        style="min-width:60px;"
                    >
                </td>

                <td align="center">
                    <select
                        class="form-control form-control-sm tipo fonte-10"
                        style="min-width:65px;"
                    >
                        <option value="" {{ (($item->tipo ?? '') == '') ? 'selected' : '' }}></option>
                        <option value="N" {{ (($item->tipo ?? '') == 'N') ? 'selected' : '' }}>N</option>
                        <option value="CV" {{ (($item->tipo ?? '') == 'CV') ? 'selected' : '' }}>CV</option>
                    </select>
                </td>

                <td align="right" class="unit_sistema">
                    {{ number_format($item->unit_sistema, 2, ',', '.') }}
                </td>

                <td align="right">
                    <input
                        type="text"
                        class="form-control form-control-sm text-right unit_manual fonte-10"
                        value="{{ number_format($item->unit_manual, 2, ',', '.') }}"
                    >
                </td>

                <td align="right" class="diferenca">
                    {{ number_format($item->diferenca, 6, ',', '.') }}
                </td>

                <td align="right">
                    <input
                        type="text"
                        class="form-control form-control-sm text-right credito_imp fonte-10"
                        value="{{ number_format($item->credito_imp * 100, 4, ',', '.') }}"
                    >
                </td>

                <td align="right" class="valor_unit">
                    {{ number_format($item->valor_unit, 2, ',', '.') }}
                </td>

                <td align="right" class="custo_liquido">
                    {{ number_format($item->custo_liquido, 2, ',', '.') }}
                </td>

                <td align="center">
                    @if(($item->utilizada_estrutura ?? 'N') == 'S')
                        <span class="badge badge-success">SIM</span>
                    @else
                        <span class="badge badge-secondary">NÃO</span>
                    @endif
                </td>

                <td class="origem" align="center">
                    @if($item->unit_manual > 0 || $item->credito_imp > 0)
                        <span class="badge badge-warning">MANUAL+ERP</span>
                    @else
                        <span class="badge badge-info">ERP</span>
                    @endif
                </td>

                <td align="center">
                    <button type="button" class="btn btn-success btn-sm btn-salvar">
                        <span class="fas fa-save"></span>
                    </button>
                </td>

                <td align="center">
                    <span class="badge badge-success salvo" style="display:none;">
                        Salvo
                    </span>
                    <span class="badge badge-danger erro" style="display:none;">
                        Erro
                    </span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="14" align="center">
                    Nenhum custo encontrado para o período informado.
                </td>
            </tr>
        @endforelse
    </tbody>

    <tfoot>
        <tr>
            <td colspan="14">
                Total de registros: <strong>{{ $totalRegistros }}</strong>
                |
                Utilizadas na estrutura: <strong>{{ $totalUtilizadas }}</strong>
                |
                Não utilizadas: <strong>{{ $totalNaoUtilizadas }}</strong>
            </td>
        </tr>
    </tfoot>
</table>
