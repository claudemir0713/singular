@extends('layouts.model')
@section('content')

    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-dollar-sign"></i> Produto
                </h3>
            </td>
        </tr>
    </table><hr>
    <div class="row">
        <div class="form-group col-md-4">
            Descrição:
            <input class="form-control" id="pro_descr" name="pro_descr">
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12">
            <table class="table table->bordered table-condensed fonte-10" id="produtosFicha">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Descrição</th>
                        <th>Qtd</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection
