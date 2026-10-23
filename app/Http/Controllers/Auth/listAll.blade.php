@extends('layouts.model')
@section('content')
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fa fa-user"></i> Usuários
                </h3>
            </td>
            <td width="50%" align="center">
            </td>
        </tr>
    </table><hr>
    <div class="row">
        <div class="form-group col-md-2">
            <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                <span class="fas fa-filter"></span> Filtros
            </button>
        </div>
    </div>
    <div class="collapse" id="collapseExample">
        <div class="card card-body">
            <form method="get" action="{{ route('usuario.listAll') }}" >
                @csrf
                <div class="row">
                    <div class="form-group col-md-5">
                        Usuário:
                        <input class="form-control" type="text" name="nome" value="{{$filtroNome}}" autocomplete="off">
                    </div>
                    <div class="form-group col-md-2">
                        Ativos:
                        <select class="form-control" name="ativo">
                            <option value="S" {{ $filtroAtivo=='S' ? 'selected' : '' }}>Sim</option>
                            <option value="N" {{ $filtroAtivo=='N' ? 'selected' : '' }}>Não</option>
                        </select>
                    </div>
                </div>
                <button class="btn btn-primary" type="submit" >
                    <span class="fas fa-play"></span> Filtrar
                </button>
            </form >
        </div>
    </div>
    <p>    <table class="table table-bordered table-condensed table-striped">
        <thead>
            <tr>
                <th width="20%">Usuário</th>
                <th width="10%">Nivel</th>
                <th width="5%">Ativo</th>
                <th width="5%">Ação</th>
            </tr>
        </thead>
        <tbody>
            {{-- {{dd($usuarios)}} --}}
            @foreach ($usuarios as $usuario)
                <tr>
                    <td >{{$usuario->name}}</td>
                    <td align="center">{{$usuario->nivel}}</td>
                    <td align="center">{{$usuario->ativo}}</td>
                    <td align="center">
                        <div class="btn-group-vertical">
                            <div class="btn-group">
                            <button type="button"  class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-cogs"></i>
                                <span>Ação</span>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{route('usuario.formEdit', [$usuario->id])}}">
                                    <i class="far fa-edit"></i>&nbsp;&nbsp;&nbsp;
                                    <span>Editar</span>
                                </a>
                            </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection


