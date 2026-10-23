@extends('layouts.model')
@section('content')

    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-boxes"></i> Estoque
                </h3>
            </td>
        </tr>
    </table><hr>
        <div class="card card-body font-12">
            <form method="get" action="{{ route('contabilidade.estoque') }}">
                @csrf
                <div class="row">
                    <div class="form-group col-md-2">
                        Ano:
                        <input class="form-control form-control-sm" type="number" id="ano" name="ano" value="{{ array_key_exists('ano',$dateForm) ? $dateForm['ano'] : date('Y') }}">
                    </div>
                    <div class="form-group col-md-2">
                        Mês:
                        <input class="form-control form-control-sm" type="number" id="mes" name="mes" value="{{ array_key_exists('mes',$dateForm) ? $dateForm['mes'] : date('m') }}">
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <span class="linkExportaContabil"></span>
                    </div>
                </div>
                <button class="btn btn-primary btn-sm font-10 btnFechaEstoque" type="button" >
                    <span class="fas fa-play font-12"></span> <span class="font-12">Fechar</span>
                </button>
            </form >
        </div>
    <p>


@endsection
