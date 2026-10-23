@extends('layouts.model')
@section('content')

    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="far fa-handshake"></i> Comissões por pagamento
                </h3>
            </td>
        </tr>
    </table><hr>

    <button class="btn btn-primary btn-sm" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
        <span class="fas fa-filter font-12"></span> <span class="font-12">Filtros</span>
    </button>
    <button class="btn btn-info  btn-sm" type="button" id="btn-imprimirComissaoPagar">
        <span class="fas fa-print  font-12"></span> Imprimir
    </button>

    <p>
    <div class="collapse" id="collapseExample">
        <div class="card card-body font-12">
            <form method="get" action="{{ route('comissao.comissaoPagar') }}">
                @csrf
                <div class="row">
                    <div class="form-group col-md-2">
                        Nf:
                        <input class="form-control form-control-sm" type="text" id="nf" name="nf" value="{{ array_key_exists('nf',$dateForm) ? $dateForm['nf'] : '' }}">
                    </div>
                    <div class="form-group col-md-1">
                        Parcela:
                        <input class="form-control form-control-sm" type="number" max="100" min="0" id="parcela" name="parcela" value="{{ array_key_exists('parcela',$dateForm) ? $dateForm['parcela'] : '' }}">
                    </div>
                    <div class="form-group col-md-3">
                        Vendedor:
                        <input class="form-control form-control-sm" type="text" id="vendedor" name="vendedor" value="{{ array_key_exists('vendedor',$dateForm) ? $dateForm['vendedor'] : '' }}">
                    </div>
                    <div class="form-group col-md-3">
                        Cliente:
                        <input class="form-control form-control-sm" type="text" id="cliente" name="cliente" value="{{ array_key_exists('cliente',$dateForm) ? $dateForm['cliente'] : '' }}">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-2">
                        De:
                        <input class="form-control form-control-sm" type="date" id="dtI" name="dtI" value="{{ array_key_exists('dtI',$dateForm) ? $dateForm['dtI'] : date('Y-m-d') }}">
                    </div>
                    <div class="form-group col-md-2">
                        Até:
                        <input class="form-control form-control-sm" type="date" id="dtF" name="dtF" value="{{ array_key_exists('dtF',$dateForm) ? $dateForm['dtF'] : date('Y-m-d') }}">
                    </div>
                </div>
                <button class="btn btn-primary btn-sm font-10" type="submit" >
                    <span class="fas fa-play font-12"></span> <span class="font-12">Filtrar</span>
                </button>
            </form >
        </div>
    </div>
    <p>


    <table class="table table-bordered table-condensed fonte-10 courier">
        <thead>
            <tr>
                <th width="5%">NF</th>
                <th width="5%">SEQ</th>
                <th width="25%">CLIENTE</th>
                <th width="5%">EMISSÃO</th>
                <th width="5%">PGTO</th>
                <th width="5%">VLR</th>
                <th width="5%">MULTA+JUROS+DESP</th>
                <th width="5%">IPI+FRETE</th>
                <th width="6%">BASE</th>
                <th width="5%">%</th>
                <th width="8%">R$ Comissão</th>
                <th width="5%">OBS</th>
            </tr>
        </thead>
        @php
            $COD_REP                = '@';
            $tot_vlrOriginal        = 0;
            $tot_juro_multa         = 0;
            $tot_ipi_frete          = 0;
            $tot_baseComissao       = 0;
            $tot_comissao           = 0;
            $tot_juro_multa_rep     = 0;
            $tot_ipi_frete_rep      = 0;
            $tot_vlrOriginal_rep    = 0;
            $tot_baseComissao_rep   = 0;
            $tot_comissao_rep       = 0;

        @endphp
        <tbody>
            {{-- {{dd($comissoes)}} --}}
            @foreach ($comissoes as $item)
                @if($COD_REP!=$item->VENDEDOR)
                    @if ($COD_REP!='@')
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
                        @php
                            $tot_vlrOriginal_rep    = 0;
                            $tot_juro_multa_rep     = 0;
                            $tot_ipi_frete_rep      = 0;
                            $tot_baseComissao_rep   = 0;
                            $tot_comissao_rep       = 0;
                        @endphp
                    @endif
                    <tr>
                        <td colspan="12" bgcolor="#d3d3d3"><b>{{$item->VENDEDOR}}</b></td>
                    </tr>
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
                    <td align="right"><span class="valor_original{{$item->CON_CODIGO}}">{{number_format($item->VLR_PAGO,2,',','.')}}</span></td>
                    <td align="right"><span class="{{$item->CON_CODIGO}}">{{number_format($item->JUROS+$item->MULTA+$DESPESA,2,',','.')}}</span></td>
                    <td align="right"><span class="{{$item->CON_CODIGO}}">{{number_format($IPI_FRETE,2,',','.')}}</span></td>
                    <td align="right"><span class="valor_pago{{$item->CON_CODIGO}}">{{number_format($BC_COMISSAO,2,',','.')}}</span></td>
                    <td align="right"><span class="perc_comissao{{$item->CON_CODIGO}}">{{number_format($item->CONC_PERC_COMISSAO,2,',','.')}}</span></td>
                    <td align="right"><span class="comissao{{$item->CON_CODIGO}}">{{number_format($BC_COMISSAO*($item->CONC_PERC_COMISSAO/100),2,',','.')}}</span></td>
                    <td align="center">{{$item->NR_CHEQUE}}</td>
                </tr>
                @php
                    $COD_REP                    = $item->VENDEDOR;

                    $tot_vlrOriginal            += $item->VLR_PAGO;
                    $tot_juro_multa             += $item->JUROS+$item->MULTA+$DESPESA;
                    $tot_ipi_frete              += $IPI_FRETE;
                    $tot_baseComissao           += $BC_COMISSAO;
                    $tot_comissao               += $BC_COMISSAO*($item->CONC_PERC_COMISSAO/100);

                    $tot_vlrOriginal_rep        += $item->VLR_PAGO;
                    $tot_juro_multa_rep         += $item->JUROS+$item->MULTA+$DESPESA;
                    $tot_ipi_frete_rep          += $IPI_FRETE;
                    $tot_baseComissao_rep       += $BC_COMISSAO;
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
@endsection
