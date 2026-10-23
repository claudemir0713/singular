@extends('layouts.model')
@section('content')
    <div class="row">
        <div class="form-group col-md-6">
            <h3>
                <i class="fas fa-dollar-sign"></i> Papeleta
            </h3>
        </div>
    </div>

<!-- Card só para visual -->
    <div class="card mb-3 fonte-10">
        <div class="card-header">Dados da Carga</div>
        <div class="card-body">
            <div class="form-row row">
                <div class="form-group col-md-1">
                    <label for="carga">Carga</label>
                    <input type="text" class="form-control fonte-10" inputmode="numeric" id="carga" name="carga" placeholder="Digite a carga">
                </div>
                <div class="form-group col-md-2">
                    <label for="carga">Placa</label>
                    <input type="text" class="form-control fonte-10 atualiza-papeleta" id="placa" name="placa" placeholder="Digite a placa">
                </div>
                <div class="form-group col-md-2">
                    <label for="carga">Motorista</label>
                    <input type="text" class="form-control fonte-10 atualiza-papeleta" id="motorista" name="motorista" placeholder="Digite o motorista">
                </div>
                <div class="form-group col-md-2">
                    <label for="carga">Vendedor</label>
                    <input type="text" class="form-control fonte-10 " id="vendedor" name="vendedor" placeholder="Filtrar por vendedor">
                </div>
                <div class="form-group col-md-2">
                    <br>
                    <a class="btn btn-primary btn-sm" href="{{ route('vendas.cargas_pdf',0) }}" id="btnPdf" target="_blank">
                        <span class="fas fa-print"></span> Gerar PDF
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div id="lista-davs"></div>

@endsection
