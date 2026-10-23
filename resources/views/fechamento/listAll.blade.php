@extends('layouts.model')
@section('content')

    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-dollar-sign"></i> Fechamento
                </h3>
            </td>
        </tr>
    </table><hr>

    <div class="collapse1" id="collapseExample">
        <div class="card card-body">
            <form method="get" action="{{ route('fechamento.fechamento') }}" target="_blank">
                @csrf
                <div class="row">
                    <div class="form-group col-md-2">
                        Ano:
                        <input class="form-control" type="number" id="ano" name="ano" value="{{ array_key_exists('ano',$dateForm) ? $dateForm['ano'] : '' }}" autofocus>
                    </div>
                    <div class="form-group col-md-2">
                        Mês:
                        <input class="form-control" type="number" id="mes" name="mes" value="{{ array_key_exists('mes',$dateForm) ? $dateForm['ano'] : '' }}">
                    </div>
                    <div class="form-group col-md-2">
                        Mostrar:
                        <select class="form-control" id="mostrar" name="mostrar">
                            <option value="N">Somente sem ficha</option>
                            <option value="S">Todos</option>
                        </select>
                    </div>

                </div>
                <button class="btn btn-primary" type="submit" >
                    <span class="fas fa-play"></span> Fechar
                </button>
            </form >
        </div>
    </div>
@endsection
