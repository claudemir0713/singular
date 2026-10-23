@extends('layouts.model')
@section('content')
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fa fa-drivers-license-o"></i> Menu Usuário
                </h3>
            </td>
        </tr>
    </table><hr>

    <div class="row">
        <div class="form-group col-md-6">
            <h3>Usuário:</h3>
            <select id="usuario">
                <option value="">Selecione</option>
                @foreach ( $usuarios as $usuario )
                    <option value="{{$usuario->id}}">{{$usuario->name}}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        <div class="form-group col-md-6">
            <table class="table  table-condensed table-borderd">
                <thead>
                    <tr>
                        <th colspan="2">Menu</th>
                    </tr>
                </thead>
                <tbody id="menuDisponivel">

                </tbody>
            </table>
        </div>
    </div>

@endsection


