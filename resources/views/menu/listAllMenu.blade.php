@extends('layouts.model')
@section('content')
    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fa fa-list"></i> Menu
                </h3>
            </td>
            <td width="50%" align="center">
                <h3>
                    <a class="cor-digiliza" href="{{route('menu.formAddmenu')}}">
                        <i class="fas fa-plus-circle"></i>&nbsp;&nbsp;&nbsp;
                        <span>Novo</span>
                    </a>
                </h3>
            </td>
        </tr>
    </table><hr>

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

    <p>
    <table class="table table-bordered table-condensed table-striped">
        <thead>
            <tr>
                <th width="20%" data-field="name">Descrição</th>
                <th width="5%" data-field="name">Icone</th>
                <th width="5%" data-field="">Ação</th>
            </tr>
        </thead>
        <tbody>
            {{-- {{dd($menus)}} --}}
            @foreach ($menus as $menu)
                @php
                    if($menu->tipo=='Título'){
                        $classTtulo='negrito';
                    }else{
                        $classTtulo='paragrafo';
                    };
                @endphp

                <tr>
                    <td class="{{$classTtulo}}">{{$menu->ordem}} - {{ $menu->descricao }} </td>
                    <td align="center"> <i class="{{ $menu->icone }}"></i> </td>

                    <td align="center">
                        <div class="btn-group-vertical">
                            <div class="btn-group">
                            <button type="button"  class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-cogs"></i>
                                <span>Ação</span>
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{route('menu.formEditmenu', $menu->id)}}">
                                    <i class="far fa-edit"></i>&nbsp;&nbsp;&nbsp;
                                    <span>Editar</span>
                                </a>
                                <a class="dropdown-item" href="#">
                                    <form action=" {{ route('menu.destroy',['menu'=> $menu->id ]) }} " method="POST">
                                        @csrf
                                        @method('delete')
                                        <input type="hidden" name='menu' value=" {{ $menu->id }} ">
                                        <i class="far fa-trash-alt"></i>
                                        <input type="submit" class="btn btn-default delete"  value="Eliminar">
                                    </form>
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


