@extends('layouts.model')
@section('content')
    <form action="" id="geraArquivo" nome="geraArquivo" method="post" target="_blank">
        @csrf
        <table class="table table-borderless table-advance table-condensed">
            <tr>
                <td width="80%">
                    <h3>
                        <i class="fas fa-hat-wizard"></i> Dados
                    </h3>
                </td>
                <td  width="20%">
                    <button type="button" class="btn btn-primary btnAtualizaEstoque">Atualiza</button>
                </td>
            </tr>
        </table><hr>
        <div class="row">
            <div class="form-group col-md-12" align="center">
                <table class="table table-condensed table-bordered">
                    <tr>
                        <td colspan="8">
                            <textarea class="form-control" id="texto" name="texto" rows="20"></textarea>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </form>
@endsection
