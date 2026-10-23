<style>
    .fonte-7{
        font-size: 7px !important;
    }
    .fonte-8{
        font-size: 8px !important;
    }
</style>
<table style="width:300px; margin-top:8px;">
    <tr>
        <td width="20%">
            {{-- <sup>Carga: </sup><strong>{{$carga['carga']}}</strong> --}}
        </td>
        <td width="30%">
            <sup>Placa: </sup><strong>{{$carga['placa']}}</strong>
        </td>
        <td width="50%">
            <sup>Motorista: </sup><strong>{{$carga['motorista']}}</strong>
        </td>
    </tr>
</table>
<hr>
<table  style="width:300px; margin-top:8px;">
    @foreach ($carga['dav'] as $dav )
        <tr>
            <td >
                {{ $loop->iteration }}º {{$dav['cliente']}}</i>
                ( {{ $dav['pedido_cli']}}</i> )
            </td>
        </tr>
        <tr>
            <td>{{ str_repeat('*', 60) }}</td>
        </tr>
        <tr>
            <td align="right">
                <table   style="width:200px; margin-top:8px;">
                    @if($dav['parcelas'])
                        @foreach ($dav['parcelas'] as $parcelas )
                            <tr>
                                <td width="10%">{{$parcelas['parcela']}}/{{$parcelas['tipo']}}</td>
                                <td width="20%">{{date('d/m/Y',strtotime($parcelas['vencimento']))}}</td>
                                <td width="40%">{{number_format($parcelas['valor'],2,',','.')}}</td>
                                <td width="30%"></td>
                            </tr>
                        @endforeach
                    @endif
                    <tr>
                        <td colspan="4"><hr></td>
                    </tr>
                    <tr>
                        <td width="10%"></td>
                        <td width="20%"><b>Frete</b></td>
                        <td width="40%">{{number_format($dav['frete_valor'],2,',','.')}}</td>
                        <td width="30%">{{$dav['cobranca']}}</td>
                    </tr>
                </table>
            </td>
        </tr>
    @endforeach
</table>



