@extends('layouts.model')
@section('content')

    <table class="table table-borderless table-advance table-condensed">
        <tr>
            <td width="80%">
                <h3>
                    <i class="fas fa-award"></i> Sped
                </h3>
            </td>
        </tr>
    </table><hr>
        <div class="card card-body font-12">
            <form method="get" action="{{ route('contabilidade.sped') }}">
                @csrf
                <div class="row">
                    <div class="form-group col-md-2">
                        De:
                        <input class="form-control form-control-sm" type="date" id="dtI" name="dtI" value="{{ array_key_exists('dtI',$dateForm) ? $dateForm['dtI'] : date('Y-m-d') }}">
                    </div>
                    <div class="form-group col-md-2">
                        Até:
                        <input class="form-control form-control-sm" type="date" id="dtF" name="dtF" value="{{ array_key_exists('dtF',$dateForm) ? $dateForm['dtF'] : date('Y-m-d') }}">
                    </div>
                    <div class="form-group col-md-2">
                        Tipo:
                        <select class="form-control" id="cod_fin" name="cod_fin">
                            <option value="0"{{ ((array_key_exists('cod_fin',$dateForm) ? $dateForm['cod_fin'] : '')=='0') ? 'selected' : '' }}>Remessa do arquivo original</option>
                            <option value="1"{{ ((array_key_exists('cod_fin',$dateForm) ? $dateForm['cod_fin'] : '')=='1') ? 'selected' : '' }}>Remessa do arquivo substituto</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group col-md-6">
                        <span class="linkExportaContabil"></span>
                    </div>
                </div>
                <button class="btn btn-primary btn-sm font-10 btnGeraSped" type="button" >
                    <span class="fas fa-play font-12"></span> <span class="font-12">Gerar</span>
                </button>
            </form >
        </div>
    <p>


@endsection
