@extends('layouts.model')
@section('content')

    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="far fa-handshake"></i> Comissões por emissão
                </h3>
            </td>
        </tr>
    </table><hr>

    <button class="btn btn-primary btn-sm" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
        <span class="fas fa-filter font-12"></span> <span class="font-12">Filtros</span>
    </button><p>
    <div class="collapse" id="collapseExample">
        <div class="card card-body font-12">
            <form method="get" action="{{ route('comissao.listAll') }}">
                @csrf
                <div class="row">
                    <div class="form-group col-md-2">
                        Nf:
                        <input class="form-control form-control-sm" type="text" id="nf" name="nf" value="{{ array_key_exists('nf',$dateForm) ? $dateForm['nf'] : '' }}">
                    </div>
                    <div class="form-group col-md-3">
                        Vendedor:
                        <input class="form-control form-control-sm" type="text" id="vendedor" name="vendedor" value="{{ array_key_exists('vendedor',$dateForm) ? $dateForm['vendedor'] : '' }}">
                    </div>
                    <div class="form-group col-md-3">
                        Cliente:
                        <input class="form-control form-control-sm" type="text" id="cliente" name="cliente" value="{{ array_key_exists('cliente',$dateForm) ? $dateForm['cliente'] : '' }}">
                    </div>                    <div class="form-group col-md-2">
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
                <th width="30%">CLIENTE</th>
                <th width="5%">EMISSÃO</th>
                <th width="5%">VCTO</th>
                <th width="5%">VLR</th>
                <th width="6%">BASE</th>
                <th width="6%">IPI_FRETE</th>
                <th width="5%">%</th>
                <th width="8%">R$ Comissão</th>
            </tr>
        </thead>
        @php
            $COD_REP                = '@';
            $tot_vlrOriginal        = 0;
            $tot_baseComissao       = 0;
            $tot_comissao           = 0;
            $tot_ipi_frete          = 0;

            $tot_vlrOriginal_rep    = 0;
            $tot_baseComissao_rep   = 0;
            $tot_comissao_rep       = 0;
            $tot_ipi_frete_rep      = 0;

        @endphp
        <tbody>
            @foreach ($comissoes as $item)
                @if($COD_REP!=$item['VENDEDOR'])
                    @if ($COD_REP!='@')
                        <tr bgcolor="#e3e3e3">
                            <td colspan="5"><b>TOTAL</b></td>
                            <td align="right">{{number_format($tot_vlrOriginal_rep,2,',','.')}}</td>
                            <td align="right">{{number_format($tot_baseComissao_rep,2,',','.')}}</td>
                            <td align="right">{{number_format($tot_ipi_frete_rep,2,',','.')}}</td>
                            <td></td>
                            <td align="right">{{number_format($tot_comissao_rep,2,',','.')}}</td>
                        </tr>
                        @php
                            $tot_vlrOriginal_rep    = 0;
                            $tot_baseComissao_rep   = 0;
                            $tot_comissao_rep       = 0;
                            $tot_ipi_frete_rep      = 0;
                        @endphp
                    @endif
                    <tr>
                        <td colspan="10" bgcolor="#d3d3d3"><b>{{$item['VENDEDOR']}}</b></td>
                    </tr>
                @endif
                @php
                    $bgcolor = '';
                    $valorOriginal = $item['CON_VALOR_ORIGINAL'];
                    $base = $item['CON_BC_COMISSAO']+$item['VLR_IPI_FRETE']+$item['FRETE_NF_PEDIDO']+$item['FRETE_PEDIDO'];
                    if(round($valorOriginal-$base,2)!=0 && $item['CON_SEQUENCIA'] == 1){
                        $bgcolor ='bgcolor=red';
                    }else{
                        $bgcolor ='';
                    }
                @endphp
                <tr class="linha{{$item['CON_CODIGO']}}" {{$bgcolor}}>
                    <td align="center">{{$item['CON_NUMERO']}}</td>
                    <td align="center">{{$item['CON_SEQUENCIA']}}</td>
                    <td>{{$item['CLIENTE']}}</td>
                    <td align="center">{{date('d/m/Y',strtotime($item['CON_DT_INCLUSAO']))}}</td>
                    <td align="center">{{date('d/m/Y',strtotime($item['CON_DT_VENCIMENTO']))}}</td>
                    <td align="right"><span class="valor_original{{$item['CON_CODIGO']}}">{{number_format($item['CON_VALOR_ORIGINAL'],2,',','.')}}</span></td>
                    <td align="right"><input type="text" size="8" class="semBorda direita baseComissao" value="{{number_format($item['CON_BC_COMISSAO'],2,',','.')}}" id="{{$item['CON_CODIGO']}}" con_codigo="{{$item['CON_CODIGO']}}" title="ENTER corrige a base"></td>
                    <td align="right"><span class="ipi_frete{{$item['CON_CODIGO']}}">{{number_format($item['VLR_IPI_FRETE']+$item['FRETE_NF_PEDIDO']+$item['FRETE_PEDIDO'],2,',','.')}}</span></td>
                    <td align="right"><span class="perc_comissao{{$item['CON_CODIGO']}}">{{number_format($item['CONC_PERC_COMISSAO'],2,',','.')}}</span></td>
                    <td align="right"><span class="comissao{{$item['CON_CODIGO']}}">{{number_format($item['CON_BC_COMISSAO']*($item['CONC_PERC_COMISSAO']/100),2,',','.')}}</span></td>
                </tr>
                @php
                    $COD_REP                    = $item['VENDEDOR'];

                    $tot_vlrOriginal            += $item['CON_VALOR_ORIGINAL'];
                    $tot_baseComissao           += $item['CON_BC_COMISSAO'];
                    $tot_comissao               += $item['CON_BC_COMISSAO']*($item['CONC_PERC_COMISSAO']/100);
                    $tot_ipi_frete              += $item['VLR_IPI_FRETE']+$item['FRETE_NF_PEDIDO']+$item['FRETE_PEDIDO'];

                    $tot_vlrOriginal_rep        += $item['CON_VALOR_ORIGINAL'];
                    $tot_baseComissao_rep       += $item['CON_BC_COMISSAO'];
                    $tot_ipi_frete_rep          += $item['VLR_IPI_FRETE']+$item['FRETE_NF_PEDIDO']+$item['FRETE_PEDIDO'];
                    $tot_comissao_rep           += $item['CON_BC_COMISSAO']*($item['CONC_PERC_COMISSAO']/100);
                    @endphp
            @endforeach
            <tr bgcolor="#e3e3e3">
                <td colspan="5"><b>TOTAL</b></td>
                <td align="right">{{number_format($tot_vlrOriginal_rep,2,',','.')}}</td>
                <td align="right">{{number_format($tot_baseComissao_rep,2,',','.')}}</td>
                <td align="right">{{number_format($tot_ipi_frete_rep,2,',','.')}}</td>
                <td></td>
                <td align="right">{{number_format($tot_comissao_rep,2,',','.')}}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">TOTAL</td>
                <td align="right">{{number_format($tot_vlrOriginal,2,',','.')}}</td>
                <td align="right">{{number_format($tot_baseComissao,2,',','.')}}</td>
                <td align="right">{{number_format($tot_ipi_frete,2,',','.')}}</td>
                <td></td>
                <td align="right">{{number_format($tot_comissao,2,',','.')}}</td>
            </tr>
        </tfoot>
    </table>
@endsection
