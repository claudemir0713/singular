@extends('layouts.model')
@section('content')

    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-dollar-sign"></i> Pagar
                </h3>
            </td>
        </tr>
    </table><hr>

    <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
        <span class="fas fa-filter"></span> Filtros
    </button><p>
    <div class="collapse" id="collapseExample">
        <div class="card card-body">
            <form method="get" action="{{ route('pagar.listAll') }}">
                @csrf
                <div class="row">
                    <div class="form-group col-md-4">
                        Fornecedor:
                        <input class="form-control" type="text" id="fornecedor" name="fornecedor" value="{{ array_key_exists('fornecedor',$dateForm) ? $dateForm['fornecedor'] : '' }}">
                    </div>
                    <div class="form-group col-md-2">
                        De:
                        <input class="form-control" type="date" id="dtI" name="dtI" value="{{ array_key_exists('dtI',$dateForm) ? $dateForm['dtI'] : '' }}">
                    </div>
                    <div class="form-group col-md-2">
                        Até:
                        <input class="form-control" type="date" id="dtF" name="dtF" value="{{ array_key_exists('dtF',$dateForm) ? $dateForm['dtF'] : '' }}">
                    </div>
                    <div class="form-group col-md-2">
                        Tipo:
                        <select class="form-control" id="tipo" name="tipo">
                            <option value="">Todos</option>
                            <option value="NF" {{ ((array_key_exists('tipo',$dateForm) ? $dateForm['tipo'] : '')=='NF') ? 'selected' : '' }}>NF</option>
                            <option value="PD" {{ ((array_key_exists('tipo',$dateForm) ? $dateForm['tipo'] : '')=='PD') ? 'selected' : '' }}>CH/PD</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary" type="submit" >
                    <span class="fas fa-play"></span> Filtrar
                </button>
            </form >
        </div>
    </div>
    <p>


    <table class="table table-bordered table-condensed fonte-10 courier">
        <thead>
            <tr>
                <th width="5%">NF</th>
                <th width="5%">PARC.</th>
                <th width="30%">FORNECEDOR</th>
                <th width="5%">EMISSÃO</th>
                <th width="5%">VENCIMENTO</th>
                <th width="8%">VALOR</th>
                <th width="5%">JUROS</th>
                <th width="5%">MULTA</th>
                <th width="5%">DESCONTO</th>
                <th width="5%">TOTAL</th>
                <th width="5%">COBRANÇA</th>

            </tr>
        </thead>
        <tbody>
            @php
                $vlr_total          = 0;
                $vlr_juros_total    = 0;
                $vlr_multa_total    = 0;
                $vlr_desconto_total = 0;
            @endphp

            @foreach ($pagar as $item )
                @php
                    $vlr_total          += $item->CON_VALOR_ORIGINAL;
                    $vlr_juros_total    += $item->CON_VALOR_JUROS;
                    $vlr_multa_total    += $item->CON_VALOR_MULTA;
                    $vlr_desconto_total += $item->CON_VALOR_DESCONTO;
                @endphp
                <tr>
                    <td>{{$item->CON_NUMERO }}</td>
                    <td align="center">{{$item->CON_SEQUENCIA }}</td>
                    <td>{{$item->PART_NOME }}</td>
                    <td align="center">{{date('d/m/Y',strtotime($item->CON_DT_INCLUSAO)) }}</td>
                    <td align="center">{{date('d/m/Y',strtotime($item->CON_DT_VENCIMENTO)) }}</td>
                    <td align="right">{{number_format($item->CON_VALOR_ORIGINAL,2,',','.') }}</td>
                    <td align="right">{{number_format($item->CON_VALOR_JUROS,2,',','.') }}</td>
                    <td align="right">{{number_format($item->CON_VALOR_MULTA,2,',','.') }}</td>
                    <td align="right">{{number_format($item->CON_VALOR_DESCONTO,2,',','.') }}</td>
                    <td align="right">{{number_format($item->CON_VALOR_ORIGINAL+$item->CON_VALOR_JUROS+$item->CON_VALOR_MULTA-$item->CON_VALOR_DESCONTO,2,',','.') }}</td>
                    <td>{{ $item->TIPO_PGTO }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">Total</td>
                <td align="right">{{number_format($vlr_total,2,',','.') }}</td>
                <td align="right">{{number_format($vlr_juros_total,2,',','.') }}</td>
                <td align="right">{{number_format($vlr_multa_total,2,',','.') }}</td>
                <td align="right">{{number_format($vlr_desconto_total,2,',','.') }}</td>
                <td align="right">{{number_format($vlr_total+$vlr_juros_total+$vlr_multa_total-$vlr_desconto_total,2,',','.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
@endsection
