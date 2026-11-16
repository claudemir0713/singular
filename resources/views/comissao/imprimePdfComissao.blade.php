<style>
    table,td,th {
        padding: 4px;
        border-collapse: collapse;
        font-family: Verdana, Helvetica, sans-serif;
        font-size: 8px;
    }
    th {
        background: #f3f3f3;
    }
    .table-line th,
    .table-line td {
        border-bottom: 1px solid #ddd;
    }
</style>

@php
    $COD_REP              = '@';
    $tot_vlrOriginal      = 0;
    $tot_baseComissao     = 0;
    $tot_comissao         = 0;
    $tot_ipi_frete        = 0;
    $tot_vlrOriginal_rep  = 0;
    $tot_baseComissao_rep = 0;
    $tot_comissao_rep     = 0;
    $tot_ipi_frete_rep    = 0;
@endphp

@foreach ($comissoes as $item)
    @if($COD_REP != $item['VENDEDOR'])
        @if($COD_REP != '@')
            <tr bgcolor="#F3F3F3">
                <td colspan="5"><b>TOTAL</b></td>
                <td align="right">{{number_format($tot_vlrOriginal_rep,2,',','.')}}</td>
                <td align="right">{{number_format($tot_baseComissao_rep,2,',','.')}}</td>
                <td align="right">{{number_format($tot_ipi_frete_rep,2,',','.')}}</td>
                <td></td>
                <td align="right">{{number_format($tot_comissao_rep,2,',','.')}}</td>
            </tr>
            </table><pagebreak />
            @php
                $tot_vlrOriginal_rep  = 0;
                $tot_baseComissao_rep = 0;
                $tot_comissao_rep     = 0;
                $tot_ipi_frete_rep    = 0;
            @endphp
        @endif
        <table class="table-line" width="100%">
            <tr>
                <td colspan="10" bgcolor="#F3F3F3"><b>{{$item['VENDEDOR']}}</b></td>
            </tr>
            <thead>
                <tr>
                    <th width="8%">NF</th>
                    <th width="4%">SEQ</th>
                    <th width="28%">CLIENTE</th>
                    <th width="9%">EMISSÃO</th>
                    <th width="9%">VCTO</th>
                    <th width="9%">VLR</th>
                    <th width="9%">BASE</th>
                    <th width="8%">IPI_FRETE</th>
                    <th width="5%">%</th>
                    <th width="11%">R$ Comissão</th>
                </tr>
            </thead>
    @endif

    @php
        $ipi_frete = $item['VLR_IPI_FRETE'] + $item['FRETE_NF_PEDIDO'] + $item['FRETE_PEDIDO'];
        $comissao  = $item['CON_BC_COMISSAO'] * ($item['CONC_PERC_COMISSAO'] / 100);
    @endphp
    <tr>
        <td align="center">{{$item['CON_NUMERO']}}</td>
        <td align="center">{{$item['CON_SEQUENCIA']}}</td>
        <td>{{$item['CLIENTE']}}</td>
        <td align="center">{{date('d/m/Y',strtotime($item['CON_DT_INCLUSAO']))}}</td>
        <td align="center">{{date('d/m/Y',strtotime($item['CON_DT_VENCIMENTO']))}}</td>
        <td align="right">{{number_format($item['CON_VALOR_ORIGINAL'],2,',','.')}}</td>
        <td align="right">{{number_format($item['CON_BC_COMISSAO'],2,',','.')}}</td>
        <td align="right">{{number_format($ipi_frete,2,',','.')}}</td>
        <td align="right">{{number_format($item['CONC_PERC_COMISSAO'],2,',','.')}}</td>
        <td align="right">{{number_format($comissao,2,',','.')}}</td>
    </tr>
    @php
        $COD_REP               = $item['VENDEDOR'];
        $tot_vlrOriginal      += $item['CON_VALOR_ORIGINAL'];
        $tot_baseComissao     += $item['CON_BC_COMISSAO'];
        $tot_ipi_frete        += $ipi_frete;
        $tot_comissao         += $comissao;
        $tot_vlrOriginal_rep  += $item['CON_VALOR_ORIGINAL'];
        $tot_baseComissao_rep += $item['CON_BC_COMISSAO'];
        $tot_ipi_frete_rep    += $ipi_frete;
        $tot_comissao_rep     += $comissao;
    @endphp
@endforeach

@if($COD_REP != '@')
        <tr bgcolor="#F3F3F3">
            <td colspan="5"><b>TOTAL</b></td>
            <td align="right">{{number_format($tot_vlrOriginal_rep,2,',','.')}}</td>
            <td align="right">{{number_format($tot_baseComissao_rep,2,',','.')}}</td>
            <td align="right">{{number_format($tot_ipi_frete_rep,2,',','.')}}</td>
            <td></td>
            <td align="right">{{number_format($tot_comissao_rep,2,',','.')}}</td>
        </tr>
    </table>
    <br>
    <table class="table-line" width="100%">
        <tr bgcolor="#e3e3e3">
            <td width="54%"><b>TOTAL GERAL</b></td>
            <td width="9%" align="right"><b>{{number_format($tot_vlrOriginal,2,',','.')}}</b></td>
            <td width="9%" align="right"><b>{{number_format($tot_baseComissao,2,',','.')}}</b></td>
            <td width="8%" align="right"><b>{{number_format($tot_ipi_frete,2,',','.')}}</b></td>
            <td width="5%"></td>
            <td width="11%" align="right"><b>{{number_format($tot_comissao,2,',','.')}}</b></td>
        </tr>
    </table>
@endif
