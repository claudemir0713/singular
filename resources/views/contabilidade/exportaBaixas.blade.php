@extends('layouts.model')
@section('content')

    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="far fa-handshake"></i> Baixas
                </h3>
            </td>
        </tr>
    </table><hr>
        <div class="card card-body font-12">
            <form method="get" action="{{ route('contabilidade.exportaBaixas') }}">
                @csrf
                <div class="row">
                    <div class="form-group col-md-2">
                        De:
                        <input class="form-control form-control-sm" type="date" id="dtI" name="dtI" value="{{ array_key_exists('dtI',$dateForm) ? $dateForm['dtI'] : date('Y-m-d') }}">
                    </div>
                    <div class="form-group col-md-2">
                        Até:
                        <input class="form-control form-control-sm" type="date" id="dtF" name="dtF" value="{{ array_key_exists('dtF',$dateForm) ? $dateForm['dtF'] : date('Y-m-d') }}">
                    </div>
                    <div class="form-group col-md-2">
                        Tipo:
                        <select class="form-control" id="tipo" name="tipo">
                            <option value="0"{{ ((array_key_exists('tipo',$dateForm) ? $dateForm['tipo'] : '')=='0') ? 'selected' : '' }}>Receber</option>
                            <option value="1"{{ ((array_key_exists('tipo',$dateForm) ? $dateForm['tipo'] : '')=='1') ? 'selected' : '' }}>Pagar</option>
                            <option value="2"{{ ((array_key_exists('tipo',$dateForm) ? $dateForm['tipo'] : '')=='2') ? 'selected' : '' }}>Transferências</option>
                            <option value="3"{{ ((array_key_exists('tipo',$dateForm) ? $dateForm['tipo'] : '')=='3') ? 'selected' : '' }}>Tarifas</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <span class="linkExportaContabil"></span>
                    </div>
                </div>
                <button class="btn btn-primary btn-sm font-10 btnExportaContabil" type="button" >
                    <span class="fas fa-play font-12"></span> <span class="font-12">Gerar</span>
                </button>
            </form >
        </div>
    <p>


@endsection
