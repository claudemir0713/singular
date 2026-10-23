@extends('layouts.model')
@section('content')
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fa fa-user"></i> Usuário
                </h3>
            </td>
        </tr>
    </table><hr>

    <div class="row">
        <div class="form-group col-md-4">
            Usuário:
            <input type='text' class="form-control" name="usuario" id="usuario" value="{{$usuario->name}}">
            <input type='hidden' name="usuario_id" id="usuario_id" value="{{$usuario->id}}">
        </div>
        <div class="form-group col-md-4">
            Login:
            <input type='text' class="form-control" name="login" id="login" value="{{$usuario->email}}">
        </div>
        <div class="form-group col-md-1">
            Adm:<br>
            <label class="switch" >
                <input type="checkbox" class="cliente_nivel" id="{{ $usuario->id }}" value="{{ $usuario->id }}" name="nivel" {{ $usuario->nivel=='adm' ? 'checked' : ''  }}>
                <span class="slider round"></span>
            </label>
        </div>
        <div class="form-group col-md-1">
            Ativo:<br>
            <label class="switch" >
                <input type="checkbox" class="cliente_ativo" id="{{ $usuario->id }}" value="{{ $usuario->id }}" name="ativo" {{ $usuario->ativo=='S' ? 'checked' : ''  }}>
                <span class="slider round"></span>
            </label>
        </div>
        <div class="form-group col-md-2">
            <br>
            <button type="button" name="sair" id="sair" value="" class="btn btn-danger btn-block">
                <span class="fa fa-door-open"></span> Sair
            </button>
        </div>

    </div>

    <div class="row">
        <div class="form-group col-md-12">
            <table class="table  table-condensed table-borderd">
                <thead>
                    <tr>
                        <th colspan="2">Etapas</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($etapas as $etapa)
                        <tr>
                            <td width="5%" style="vertical-align: middle">
                                <label class="switch" >
                                            <input type="checkbox" class="etapa_disponivel" name="etapa" etapa_id="{{ $etapa->id }}" usuario_id="{{ $usuario->id }}" value="{{ $etapa->etapa_id }}"  {{ $etapa->etapa_id ? 'checked' : '' }} >
                                    <span class="slider round"></span>
                                </label>
                            </td>
                            <td  width="95%" style="vertical-align: middle">
                                {{ $etapa->etapa }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <script>
        $(document).ready(function(){

            $('button#sair').click(function(){
                $(location).attr('href',url+'/usuario');
            })
        })
    </script>

@endsection


