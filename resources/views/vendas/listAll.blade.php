@extends('layouts.model')
@section('content')

    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-dollar-sign"></i> Vendas
                </h3>
            </td>
        </tr>
    </table><hr>

    <button class="btn btn-primary btn-sm" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
        <span class="fas fa-filter font-12"></span> <span class="font-12">Filtros</span>
    </button><p>
    <div class="collapse" id="collapseExample">
        <div class="card card-body">
            <form method="get" action="{{ route('vendas.listAll') }}">
                @csrf
                <div class="row">
                    <div class="form-group col-md-2">
                        NF:
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
                <button class="btn btn-primary  btn-sm" type="submit" >
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
                <th width="5%">TIPO</th>
                <th width="26%">VENDEDOR</th>
                <th width="30%">CLIENTE</th>
                <th width="5%">EMISSÃO</th>
                <th width="5%">QTD</th>
                <th width="8%">FAT1</th>
                <th width="8%">FAT2</th>
                <th width="8%">IPI</th>
                <th width="8%">FRETE</th>
                <th width="8%">FAT.TOTAL</th>
            </tr>
        </thead>
        @php
            $qtd_total = 0;
            $fat_total1 = 0;
            $fat_total2 = 0;
            $ipi = 0;
            $frete = 0;
        @endphp
        <tbody>
            @foreach ( $vendas as $item )
                @php
                    $qtd_total +=$item->QUANTIDADE;
                    $fat_total1 +=$item->FAT_BRUTO;
                    $fat_total2 +=$item->FAT_BRUTO_2;
                    $ipi +=$item->IPI;
                    $frete +=$item->FRETE;
                @endphp
                <tr>
                    <td align="center">{{$item->DOCUMENTO}}</td>
                    <td align="center">{{$item->TIPO_RECEITA}}</td>
                    <td>{{$item->VENDEDOR}}</td>
                    <td>{{$item->CLIENTE}}</td>
                    <td align="center">{{date('d/m/Y',strtotime($item->DATA_COMP))}}</td>
                    <td align="right">{{number_format($item->QUANTIDADE,4,',','.')}}</td>
                    <td align="right">{{number_format($item->FAT_BRUTO,2,',','.')}}</td>
                    <td align="right">{{number_format($item->FAT_BRUTO_2,2,',','.')}}</td>
                    <td align="right">{{number_format($item->IPI,2,',','.')}}</td>
                    <td align="right">{{number_format($item->FRETE,2,',','.')}}</td>
                    <td align="right">{{number_format($item->FAT_BRUTO+$item->FAT_BRUTO_2+$item->IPI,2,',','.')}}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">TOTAL</td>
                <td align="right">{{number_format($qtd_total,4,',','.')}}</td>
                <td align="right">{{number_format($fat_total1,2,',','.')}}</td>
                <td align="right">{{number_format($fat_total2,2,',','.')}}</td>
                <td align="right">{{number_format($ipi,2,',','.')}}</td>
                <td align="right">{{number_format($frete,2,',','.')}}</td>
                <td align="right">{{number_format($fat_total1+$fat_total2+$ipi,2,',','.')}}</td>
            </tr>
        </tfoot>
    </table>
@endsection
