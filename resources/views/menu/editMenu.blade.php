@extends('layouts.model')

@section('content')
    @if (session()->get('success'))
        <div class="alert alert-success">
            {{ session()->get('success') }}
        </div>
        <br/>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <br/>
    @endif
    <h3 class=""><i class="fa fa-list"></i> Menu</h3>
    <form action="" id="cadastro-menu" nome="cadastro-menu" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="route" id="route" value="/menu/edit/{{$menu->id}}">
        <input type="hidden" name="type" id="type" value="PATCH">

        <div class="row">
            <div class="form-group col-md-2">
                Tipo:
                <select name="tipo" id="tipo">
                    <option value="Título" {{$menu->tipo=='Titulo' ? 'selected' : ''}}>Título</option>
                    <option value="Link" {{$menu->tipo=='Link' ? 'selected' : ''}}>Link</option>
                </select>
            </div>
            <div class="form-group col-md-2">
                Ordem:
                <input class="form-control" type="text" name="ordem" id="ordem"  value="{{$menu->ordem}}" >
            </div>
            <div class="form-group col-md-6">
                Descrição:
                <input class="form-control" type="text" name="descricao" id="descricao" value="{{$menu->descricao}}" >
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-5">
                Rota:
                <input class="form-control" type="text" name="rota" id="rota"  value="{{$menu->rota}}" >
            </div>
            <div class="form-group col-md-5">
                Icone:
                <input class="form-control" type="text" name="icone" id="icone"  value="{{$menu->icone}}" >
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-3">
                <button type="submit" name="salvar" value="" id="salvar" class="btn btn-success btn-block">
                    <span class="fas fa-save"></span> Salvar
                </button>
            </div>
                <div class="form-group col-md-3">
                    <button type="button" name="sair" id="sair" value="" class="btn btn-danger btn-block">
                        <span class="fa fa-door-open"></span> Sair
                    </button>
                </div>
            </div>
        </div>
    </form>

    <script>
        $(document).ready(function(){

            $('button#sair').click(function(){
                $(location).attr('href',url+'/menu');
            })
        })
    </script>

@endsection
