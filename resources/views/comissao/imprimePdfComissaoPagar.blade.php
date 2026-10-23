<style>
    table,td,th {
        padding: 5px;
        border-collapse: collapse;
        font-family:'Verdana, Helvetica, sans-serif';
        /* font-family: 'Courier New', Courier, monospace; */
        font-size: 8px;
    }
    th {
        align:center;
        background: #f3f3f3;
    }
    .table-bordered th,
    .table-bordered td {
        border: 1px solid #ddd !important;
    }
    .table-line th,
    .table-line td {
        border-bottom: 1px solid #ddd !important;
    }
    .middle{
        vertical-align: middle !important;
    }
    .linha {
        border-bottom: 1px solid #ddd !important;
    }
    .topo {
        border-top: 1px solid #ddd !important;
    }
    .fonte-10{
        font-size: 10px !important;
    }
    .fonte-12{
        font-size: 12px !important;
    }

    .fonte-18{
        font-size: 18px !important;
    }
    .fonte-22{
        font-size: 22px !important;
    }
    .fonte-vermelha{
        color: red !important;
    }
</style>

    @php
        $COD_REP                = '@';
        $tot_vlrOriginal        = 0;
        $tot_baseComissao       = 0;
        $tot_comissao           = 0;
        $tot_vlrOriginal_rep    = 0;
        $tot_baseComissao_rep   = 0;
        $tot_comissao_rep       = 0;
        $tot_juro_multa         = 0;
        $tot_juro_multa_rep     = 0;
        $tot_ipi_frete          = 0;
        $tot_ipi_frete_rep      = 0;


    @endphp
    <tbody>
        {{-- {{dd($comissoes)}} --}}
        @foreach ($comissoes as $item)
            @if($COD_REP!=$item->VENDEDOR)
                @if ($COD_REP!='@')
                    <tr bgcolor="#F3F3F3">
                        <td colspan="5"><b>TOTAL</b></td>
                        <td align="right">{{number_format($tot_vlrOriginal_rep,2,',','.')}}</td>
                        <td align="right">{{number_format($tot_baseComissao_rep,2,',','.')}}</td>
                        <td></td>
                        <td align="right">{{number_format($tot_comissao_rep,2,',','.')}}</td>
                    </tr>
                    </table><pagebreak />
                    @php
                        $tot_vlrOriginal_rep    = 0;
                        $tot_baseComissao_rep   = 0;
                        $tot_comissao_rep       = 0;
                        $tot_juro_multa_rep     = 0;
                        $tot_ipi_frete_rep      = 0;
                    @endphp

                @endif
                {{-- </table> --}}
                <table class="table-line"  width="100%">
                        <tr>
                            <td colspan="12" bgcolor="#F3F3F3"><b>{{$item->VENDEDOR}}</b></td>
                        </tr>
                        <thead>
                        <tr>
                            <th width="7%">NF</th>
                            <th width="2%">SEQ</th>
                            <th width="20%">CLIENTE</th>
                            <th width="5%">EMISSÃO</th>
                            <th width="7%">PGTO</th>
                            <th width="7%">VLR</th>
                            <th width="5%">MULTA+JUROS+DESP</th>
                            <th width="5%">IPI+FRETE</th>
                            <th width="6%">BASE</th>
                            <th width="5%">%</th>
                            <th width="8%">R$ Comissão</th>
                            <th width="5%">OBS</th>
                        </tr>
                    </thead>
            @endif

                @php
                    $PERC = $item->VLR_PAGO/($item->CON_VALOR_ORIGINAL + $item->MULTA + $item->DESPESA);
                    $DESPESA = $item->DESPESA * $PERC;

                    $VLR_PAGO = $item->VLR_PAGO - $item->JUROS - $item->MULTA - $DESPESA;
                    $BC_COMISSAO =  $VLR_PAGO *($item->CON_BC_COMISSAO / $item->CON_VALOR_ORIGINAL);
                    $IPI_FRETE = $item->VLR_PAGO - ($item->JUROS+$item->MULTA+$DESPESA) - $BC_COMISSAO;
                    if(trim($item->TIPO_BAIXA) =='CHQ'){
                        $CON_NR = $item->CON_NUMERO."\n";
                    }else{
                        $CON_NR = $item->CON_NUMERO;
                    }
                @endphp

                    <tr class="linha{{$item->CON_CODIGO}}">
                        <td align="center">{{$CON_NR}}</td>
                        <td align="center">{{$item->CON_SEQUENCIA}}</td>
                        <td>{{$item->CLIENTE}}</td>
                        <td align="center">{{date('d/m/Y',strtotime($item->CON_DT_INCLUSAO))}}</td>
                        <td align="center">{{date('d/m/Y',strtotime($item->DT_PAGAMENTO))}}</td>
                        <td align="right"><span class="valor_original{{$item->CON_CODIGO}}">{{number_format($VLR_PAGO,2,',','.')}}</span></td>
                        <td align="right"><span class="{{$item->CON_CODIGO}}">{{number_format($item->JUROS+$item->MULTA+$DESPESA,2,',','.')}}</span></td>
                        <td align="right"><span class="{{$item->CON_CODIGO}}">{{number_format($IPI_FRETE,2,',','.')}}</span></td>
                        <td align="right"><span class="valor_pago{{$item->CON_CODIGO}}">{{number_format($BC_COMISSAO,2,',','.')}}</span></td>
                        <td align="right"><span class="perc_comissao{{$item->CON_CODIGO}}">{{number_format($item->CONC_PERC_COMISSAO,2,',','.')}}</span></td>
                        <td align="right"><span class="comissao{{$item->CON_CODIGO}}">{{number_format($BC_COMISSAO*($item->CONC_PERC_COMISSAO/100),2,',','.')}}</span></td>
                        <td align="center">{{$item->NR_CHEQUE}}</td>
                    </tr>
                    @php
                    $COD_REP                    = $item->VENDEDOR;

                    $tot_vlrOriginal            += $VLR_PAGO;
                    $tot_juro_multa             += $item->JUROS+$item->MULTA+$DESPESA;
                    $tot_ipi_frete              += $IPI_FRETE;

                    $tot_baseComissao           += $BC_COMISSAO;
                    $tot_comissao               += $BC_COMISSAO*($item->CONC_PERC_COMISSAO/100);

                    $tot_vlrOriginal_rep        += $VLR_PAGO;
                    $tot_baseComissao_rep       += $BC_COMISSAO;
                    $tot_juro_multa_rep         += $item->JUROS+$item->MULTA+$DESPESA;
                    $tot_ipi_frete_rep          += $IPI_FRETE;

                    $tot_comissao_rep           += $BC_COMISSAO*($item->CONC_PERC_COMISSAO/100);
                    @endphp
            @endforeach
            <tr bgcolor="#e3e3e3">
                <td colspan="5"><b>TOTAL</b></td>
                <td align="right">{{number_format($tot_vlrOriginal_rep,2,',','.')}}</td>
                <td align="right">{{number_format($tot_juro_multa_rep,2,',','.')}}</td>
                <td align="right">{{number_format($tot_ipi_frete_rep,2,',','.')}}</td>
                <td align="right">{{number_format($tot_baseComissao_rep,2,',','.')}}</td>
                <td></td>
                <td align="right">{{number_format($tot_comissao_rep,2,',','.')}}</td>
                <td></td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">TOTAL</td>
                <td align="right">{{number_format($tot_vlrOriginal,2,',','.')}}</td>
                <td align="right">{{number_format($tot_juro_multa,2,',','.')}}</td>
                <td align="right">{{number_format($tot_ipi_frete,2,',','.')}}</td>
                <td align="right">{{number_format($tot_baseComissao,2,',','.')}}</td>
                <td></td>
                <td align="right">{{number_format($tot_comissao,2,',','.')}}</td>
                <td></td>
            </tr>
        </tfoot>

</table>
