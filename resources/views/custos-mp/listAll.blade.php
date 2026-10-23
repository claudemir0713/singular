@extends('layouts.model')

@section('content')

<style>
    :root {
        --custos-topo-altura: 132px;
        --custos-topo-offset: 0px;
    }

    .custosmp-topo {
        position: sticky;
        top: var(--custos-topo-offset);
        z-index: 1000;
        background: #eeeeee;
        padding: 8px 0 10px 0;
        border-bottom: 1px solid #d6d6d6;
    }

    .custosmp-title-bar {
        margin-bottom: 6px;
    }

    .custosmp-title-bar table {
        margin-bottom: 0;
    }

    .custosmp-title-bar h3 {
        margin: 0;
        font-size: 24px;
        line-height: 1.2;
    }

    .custosmp-title-bar hr {
        margin-top: 8px;
        margin-bottom: 10px;
    }

    .custosmp-filtros {
        padding: 12px 22px 8px 22px;
        margin-bottom: 0;
    }

    .custosmp-filtros .form-group {
        margin-bottom: 6px;
    }

    .custosmp-filtros label {
        margin-bottom: 4px;
        font-size: 13px;
    }

    .custosmp-filtros .form-control {
        height: 31px;
        padding-top: 4px;
        padding-bottom: 4px;
    }

    .custosmp-filtros .btn {
        height: 31px;
    }

    #areaTabelaCustosMp {
        margin-top: 16px;
    }

    #areaTabelaCustosMp .tabela-custos-mp thead th {
        position: sticky;
        top: var(--custos-topo-altura);
        z-index: 900;
        background: #c7c7c7;
        box-shadow: 0 1px 0 #b5b5b5;
    }

    .input-custo-mp {
        min-width: 90px;
        max-width: 110px;
    }

    .badge {
        font-size: 10px;
    }

    #textoImportacaoCustos {
        font-family: Consolas, Courier New, monospace;
        font-size: 12px;
        min-height: 330px;
        white-space: pre;
    }

    .resultado-importacao {
        display: none;
        margin-top: 12px;
    }

    .resultado-importacao pre {
        background: #f7f7f7;
        border: 1px solid #ddd;
        padding: 10px;
        font-size: 12px;
        max-height: 220px;
        overflow: auto;
    }
</style>

<div class="custosmp-topo" id="topoCustosMp">

    <div class="custosmp-title-bar">
        <table class="table table-borderless table-advance table-condensed">
            <tr>
                <td width="70%">
                    <h3>
                        <i class="fas fa-cubes"></i> Custos MP
                    </h3>
                </td>

                <td width="30%" align="right">
                    <button
                        type="button"
                        class="btn btn-warning btn-sm fonte-10"
                        data-toggle="modal"
                        data-target="#modalImportarCustosMp"
                    >
                        <i class="fas fa-file-import"></i> Importar custos
                    </button>

                    <span class="badge badge-info">
                        {{ str_pad($mes, 2, '0', STR_PAD_LEFT) }}/{{ $ano }}
                    </span>
                </td>
            </tr>
        </table>
        <hr>
    </div>

    <div class="card card-body custosmp-filtros">
        <form action="" method="GET" id="formFiltrosCustosMp">

            <div class="row align-items-end">

                <div class="form-group col-md-2">
                    <label>
                        <i class="fas fa-calendar-alt"></i> Ano
                    </label>
                    <input
                        class="form-control filtro-auto fonte-10"
                        type="number"
                        id="ano"
                        name="ano"
                        value="{{ $ano }}"
                    >
                </div>

                <div class="form-group col-md-2">
                    <label>
                        <i class="fas fa-calendar"></i> Mês
                    </label>
                    <input
                        class="form-control filtro-auto fonte-10"
                        type="number"
                        id="mes"
                        name="mes"
                        min="1"
                        max="12"
                        value="{{ $mes }}"
                    >
                </div>

                <div class="form-group col-md-4">
                    <label>
                        <i class="fas fa-search"></i> Buscar
                    </label>
                    <input
                        class="form-control filtro-busca fonte-10"
                        type="text"
                        id="busca"
                        name="busca"
                        placeholder="Código ou descrição da matéria-prima"
                        value="{{ $busca ?? '' }}"
                        autocomplete="off"
                    >
                </div>

                <div class="form-group col-md-2">
                    <label>
                        <i class="fas fa-check-circle"></i> Utilizada na estrutura
                    </label>
                    <select
                        class="form-control filtro-auto fonte-10"
                        id="utilizada"
                        name="utilizada"
                    >
                        <option value="" {{ (($utilizada ?? '') == '') ? 'selected' : '' }}>
                            Todos
                        </option>

                        <option value="S" {{ (($utilizada ?? '') == 'S') ? 'selected' : '' }}>
                            Sim
                        </option>

                        <option value="N" {{ (($utilizada ?? '') == 'N') ? 'selected' : '' }}>
                            Não
                        </option>
                    </select>
                </div>

                <div class="form-group col-md-1">
                    <button class="btn btn-primary btn-block btn-sm fonte-10" type="submit">
                        <span class="fas fa-play"></span> Filtrar
                    </button>
                </div>

            </div>

        </form>
    </div>
</div>

<div id="areaTabelaCustosMp">
    @include('custos-mp._table')
</div>

<div class="modal fade" id="modalImportarCustosMp" tabindex="-1" role="dialog" aria-labelledby="modalImportarCustosMpLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalImportarCustosMpLabel">
                    <i class="fas fa-file-import"></i> Importar custos manuais de MP
                </h5>

                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">

                <div class="alert alert-info fonte-10">
                    Cole abaixo os dados copiados do Excel com as colunas:
                    <strong>CÓDIGO, DESCRIÇÃO, UNID_MED, TIPO, VALOR_UNIT, CRÉDITO_IMP, CUSTO_LIQUIDO</strong>.
                    <br>
                    O sistema atualizará <strong>UNID_MED</strong>, <strong>TIPO</strong>, usará o <strong>VALOR_UNIT</strong> como custo manual e recalculará o custo líquido.
                </div>

                <div class="row">
                    <div class="form-group col-md-2">
                        Ano:
                        <input
                            type="number"
                            class="form-control fonte-10"
                            id="anoImportacaoCustos"
                            value="{{ $ano }}"
                        >
                    </div>

                    <div class="form-group col-md-2">
                        Mês:
                        <input
                            type="number"
                            class="form-control fonte-10"
                            id="mesImportacaoCustos"
                            min="1"
                            max="12"
                            value="{{ $mes }}"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <textarea
                        class="form-control"
                        id="textoImportacaoCustos"
                        placeholder="Cole aqui os dados copiados do Excel: CÓDIGO, DESCRIÇÃO, UNID_MED, TIPO, VALOR_UNIT, CRÉDITO_IMP, CUSTO_LIQUIDO..."
                    ></textarea>
                </div>

                <div class="resultado-importacao alert alert-secondary" id="resultadoImportacaoCustos">
                    <strong>Resultado da importação:</strong>
                    <pre id="resultadoImportacaoCustosTexto"></pre>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    Fechar
                </button>

                <button type="button" class="btn btn-warning btn-sm" id="btnLimparImportacaoCustos">
                    <i class="fas fa-eraser"></i> Limpar
                </button>

                <button type="button" class="btn btn-primary btn-sm" id="btnProcessarImportacaoCustos">
                    <i class="fas fa-cogs"></i> Processar importação
                </button>
            </div>

        </div>
    </div>
</div>

<script>
window.timerFiltroCustosMp = window.timerFiltroCustosMp || null;
window.ajaxFiltroCustosMp = window.ajaxFiltroCustosMp || null;
window.custosMpNavegando = false;

function ajustarTopoTabelaCustosMp() {
    var alturaTopo = $('#topoCustosMp').outerHeight();

    if (alturaTopo && alturaTopo > 0) {
        document.documentElement.style.setProperty('--custos-topo-altura', alturaTopo + 'px');
    }
}

function moedaParaNumero(valor) {
    if (!valor) {
        return 0;
    }

    valor = valor.toString().trim();
    valor = valor.replace(/\./g, '');
    valor = valor.replace(',', '.');

    return parseFloat(valor) || 0;
}

function numeroParaMoeda(valor, casas) {
    casas = casas === undefined ? 6 : casas;

    return parseFloat(valor || 0).toLocaleString('pt-BR', {
        minimumFractionDigits: casas,
        maximumFractionDigits: casas
    });
}

function recalcularLinha($tr) {
    var unitSistema = moedaParaNumero($tr.find('.unit_sistema').text());
    var unitManual = moedaParaNumero($tr.find('.unit_manual').val());
    var creditoDigitado = moedaParaNumero($tr.find('.credito_imp').val());

    var creditoImp = creditoDigitado > 1
        ? creditoDigitado / 100
        : creditoDigitado;

    var valorUnit = unitManual > 0
        ? unitManual
        : unitSistema;

    var diferenca = unitManual > 0
        ? unitManual - unitSistema
        : 0;

    var custoLiquido = valorUnit * (1 - creditoImp);

    $tr.find('.diferenca').text(numeroParaMoeda(diferenca, 6));
    $tr.find('.valor_unit').text(numeroParaMoeda(valorUnit, 2));
    $tr.find('.custo_liquido').text(numeroParaMoeda(custoLiquido, 2));

    if (unitManual > 0 || creditoImp > 0) {
        $tr.find('.origem').html('<span class="badge badge-warning">MANUAL+ERP</span>');
    } else {
        $tr.find('.origem').html('<span class="badge badge-info">ERP</span>');
    }
}

function carregarCustosMp() {
    var $form = $('#formFiltrosCustosMp');
    var url = $form.attr('action');
    var dados = $form.serialize();

    if (window.ajaxFiltroCustosMp) {
        window.ajaxFiltroCustosMp.abort();
    }

    $('#areaTabelaCustosMp').css('opacity', '0.45');

    window.ajaxFiltroCustosMp = $.ajax({
        url: url,
        type: 'GET',
        data: dados,
        success: function (html) {
            $('#areaTabelaCustosMp').html(html);

            var novaUrl = url + '?' + dados;
            window.history.replaceState({}, '', novaUrl);

            ajustarTopoTabelaCustosMp();
        },
        error: function (xhr) {
            if (xhr.status !== 0) {
                alert('Erro ao carregar filtros.');
            }
        },
        complete: function () {
            $('#areaTabelaCustosMp').css('opacity', '1');
            window.ajaxFiltroCustosMp = null;
        }
    });
}

function focarCampoCustosMp($campo) {
    if (!$campo || !$campo.length) {
        return;
    }

    if (window.custosMpNavegando) {
        return;
    }

    window.custosMpNavegando = true;

    setTimeout(function () {
        $campo.focus();
        $campo.select();

        setTimeout(function () {
            window.custosMpNavegando = false;
        }, 90);
    }, 20);
}

function obterLinhasEditaveisCustosMp() {
    return $('#areaTabelaCustosMp tbody tr').filter(function () {
        return $(this).find('.unit_manual, .credito_imp').length > 0;
    });
}

$(document).ready(function () {
    ajustarTopoTabelaCustosMp();
});

$(window).off('resize.custosMp');
$(window).on('resize.custosMp', function () {
    ajustarTopoTabelaCustosMp();
});

$(document).off('submit.custosMpFiltro', '#formFiltrosCustosMp');
$(document).on('submit.custosMpFiltro', '#formFiltrosCustosMp', function (e) {
    e.preventDefault();
    carregarCustosMp();
});

$(document).off('change.custosMpFiltro', '.filtro-auto');
$(document).on('change.custosMpFiltro', '.filtro-auto', function () {
    carregarCustosMp();
});

$(document).off('keyup.custosMpFiltro', '.filtro-busca');
$(document).on('keyup.custosMpFiltro', '.filtro-busca', function (e) {
    clearTimeout(window.timerFiltroCustosMp);

    if (e.which === 13 || e.keyCode === 13) {
        carregarCustosMp();
        return;
    }

    var busca = $(this).val().trim();

    window.timerFiltroCustosMp = setTimeout(function () {
        if (busca.length === 0 || busca.length >= 3) {
            carregarCustosMp();
        }
    }, 900);
});

$(document).off('keyup.custosMpCalc change.custosMpCalc', '.unit_manual, .credito_imp, .unid_med, .tipo');
$(document).on('keyup.custosMpCalc change.custosMpCalc', '.unit_manual, .credito_imp, .unid_med, .tipo', function () {
    var $tr = $(this).closest('tr');

    $tr.find('.salvo').hide();
    $tr.find('.erro').hide();

    recalcularLinha($tr);
});

$(document).off('focus.custosMpNav', '.unit_manual, .credito_imp');
$(document).on('focus.custosMpNav', '.unit_manual, .credito_imp', function () {
    var input = this;

    setTimeout(function () {
        input.select();
    }, 50);
});

$(document).off('keydown.custosMpNav', '.unit_manual, .credito_imp');
$(document).on('keydown.custosMpNav', '.unit_manual, .credito_imp', function (e) {
    var tecla = e.which || e.keyCode;

    var TAB = 9;
    var ENTER = 13;
    var SETA_ESQUERDA = 37;
    var SETA_CIMA = 38;
    var SETA_DIREITA = 39;
    var SETA_BAIXO = 40;

    var teclaNavegacao =
        tecla === TAB ||
        tecla === ENTER ||
        tecla === SETA_ESQUERDA ||
        tecla === SETA_CIMA ||
        tecla === SETA_DIREITA ||
        tecla === SETA_BAIXO;

    if (!teclaNavegacao) {
        return;
    }

    if (window.custosMpNavegando && tecla !== ENTER) {
        e.preventDefault();
        return;
    }

    var $inputAtual = $(this);
    var $trAtual = $inputAtual.closest('tr');
    var $linhas = obterLinhasEditaveisCustosMp();
    var indiceLinhaAtual = $linhas.index($trAtual);

    var campoAtual = $inputAtual.hasClass('unit_manual')
        ? 'unit_manual'
        : 'credito_imp';

    if (tecla === ENTER) {
        e.preventDefault();

        if (!$trAtual.data('salvando')) {
            $trAtual.find('.btn-salvar').trigger('click');
        }

        return;
    }

    if (tecla === TAB) {
        e.preventDefault();

        if (e.shiftKey) {
            if ($inputAtual.hasClass('credito_imp')) {
                focarCampoCustosMp($trAtual.find('.unit_manual'));
            } else {
                var $linhaAnteriorTab = $linhas.eq(indiceLinhaAtual - 1);

                if ($linhaAnteriorTab.length) {
                    focarCampoCustosMp($linhaAnteriorTab.find('.credito_imp'));
                }
            }
        } else {
            if ($inputAtual.hasClass('unit_manual')) {
                focarCampoCustosMp($trAtual.find('.credito_imp'));
            } else {
                var $proximaLinhaTab = $linhas.eq(indiceLinhaAtual + 1);

                if ($proximaLinhaTab.length) {
                    focarCampoCustosMp($proximaLinhaTab.find('.unit_manual'));
                }
            }
        }

        return;
    }

    if (tecla === SETA_BAIXO) {
        e.preventDefault();

        var $proximaLinha = $linhas.eq(indiceLinhaAtual + 1);

        if ($proximaLinha.length) {
            focarCampoCustosMp($proximaLinha.find('.' + campoAtual));
        }

        return;
    }

    if (tecla === SETA_CIMA) {
        e.preventDefault();

        var $linhaAnterior = $linhas.eq(indiceLinhaAtual - 1);

        if ($linhaAnterior.length) {
            focarCampoCustosMp($linhaAnterior.find('.' + campoAtual));
        }

        return;
    }

    if (tecla === SETA_DIREITA) {
        e.preventDefault();

        if ($inputAtual.hasClass('unit_manual')) {
            focarCampoCustosMp($trAtual.find('.credito_imp'));
        } else {
            var $proximaLinhaDireita = $linhas.eq(indiceLinhaAtual + 1);

            if ($proximaLinhaDireita.length) {
                focarCampoCustosMp($proximaLinhaDireita.find('.unit_manual'));
            }
        }

        return;
    }

    if (tecla === SETA_ESQUERDA) {
        e.preventDefault();

        if ($inputAtual.hasClass('credito_imp')) {
            focarCampoCustosMp($trAtual.find('.unit_manual'));
        } else {
            var $linhaAnteriorEsquerda = $linhas.eq(indiceLinhaAtual - 1);

            if ($linhaAnteriorEsquerda.length) {
                focarCampoCustosMp($linhaAnteriorEsquerda.find('.credito_imp'));
            }
        }

        return;
    }
});

$(document).off('click.custosMpSalvar', '.btn-salvar');
$(document).on('click.custosMpSalvar', '.btn-salvar', function () {
    var $btn = $(this);
    var $tr = $btn.closest('tr');

    if ($tr.data('salvando')) {
        return;
    }

    $tr.data('salvando', true);

    $tr.find('.salvo').hide();
    $tr.find('.erro').hide();

    $btn.prop('disabled', true);
    $btn.html('<span class="fas fa-spinner fa-spin"></span>');

    $.ajax({
        url: "{{ route('custosMp.salvar-manual') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            ano: $tr.data('ano'),
            mes: $tr.data('mes'),
            cod_mp: $tr.data('cod'),
            unit_manual: $tr.find('.unit_manual').val(),
            credito_imp: $tr.find('.credito_imp').val(),
            unid_med: $tr.find('.unid_med').val(),
            tipo: $tr.find('.tipo').val()
        },
        success: function (response) {
            if (response.success) {
                var custo = response.custo;
                var linhasAtualizadas = response.linhas_ficha_atualizadas || 0;
                var produtosAtualizados = response.produtos_estrutura_atualizados || 0;

                $tr.find('.unid_med').val(custo.unid_med || '');
                $tr.find('.tipo').val(custo.tipo || '');
                $tr.find('.unit_manual').val(numeroParaMoeda(custo.unit_manual, 2));
                $tr.find('.diferenca').text(numeroParaMoeda(custo.diferenca, 6));
                $tr.find('.valor_unit').text(numeroParaMoeda(custo.valor_unit, 2));
                $tr.find('.credito_imp').val(numeroParaMoeda(custo.credito_imp * 100, 4));
                $tr.find('.custo_liquido').text(numeroParaMoeda(custo.custo_liquido, 2));

                if (parseFloat(custo.unit_manual) > 0 || parseFloat(custo.credito_imp) > 0) {
                    $tr.find('.origem').html('<span class="badge badge-warning">MANUAL+ERP</span>');
                } else {
                    $tr.find('.origem').html('<span class="badge badge-info">ERP</span>');
                }

                $tr.find('.salvo')
                    .removeClass('badge-warning')
                    .addClass('badge-success')
                    .html('Salvo | Fichas: ' + linhasAtualizadas + ' | Produtos: ' + produtosAtualizados)
                    .fadeIn();

                setTimeout(function () {
                    $tr.find('.salvo').fadeOut();
                }, 5000);
            } else {
                $tr.find('.erro').fadeIn();
                alert(response.message || 'Erro ao salvar custo manual.');
            }
        },
        error: function (xhr) {
            $tr.find('.erro').fadeIn();

            var mensagem = 'Erro ao salvar custo manual.';

            if (xhr.responseJSON && xhr.responseJSON.erro) {
                mensagem = xhr.responseJSON.erro;
            }

            alert(mensagem);
        },
        complete: function () {
            $tr.data('salvando', false);
            $btn.prop('disabled', false);
            $btn.html('<span class="fas fa-save"></span>');
        }
    });
});

$(document).off('click.custosMpImportarLimpar', '#btnLimparImportacaoCustos');
$(document).on('click.custosMpImportarLimpar', '#btnLimparImportacaoCustos', function () {
    $('#textoImportacaoCustos').val('');
    $('#resultadoImportacaoCustos').hide();
    $('#resultadoImportacaoCustosTexto').text('');
});

$(document).off('click.custosMpImportarProcessar', '#btnProcessarImportacaoCustos');
$(document).on('click.custosMpImportarProcessar', '#btnProcessarImportacaoCustos', function () {
    var $btn = $(this);
    var texto = $('#textoImportacaoCustos').val();
    var ano = $('#anoImportacaoCustos').val();
    var mes = $('#mesImportacaoCustos').val();

    if (!texto || texto.trim() === '') {
        alert('Cole os dados do Excel antes de processar.');
        return;
    }

    $btn.prop('disabled', true);
    $btn.html('<span class="fas fa-spinner fa-spin"></span> Processando...');

    $('#resultadoImportacaoCustos').show();
    $('#resultadoImportacaoCustosTexto').text('Processando importação, aguarde...');

    $.ajax({
        url: "{{ route('custosMp.importar-manual-lote') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            ano: ano,
            mes: mes,
            texto: texto
        },
        success: function (response) {
            if (response.success) {
                var importacao = response.importacao || {};
                var aplicacao = response.aplicacao_mp || {};
                var estrutura = response.estrutura || {};

                var textoResultado = '';

                textoResultado += 'Importação concluída com sucesso.' + "\n\n";
                textoResultado += 'Linhas lidas: ' + (importacao.linhas_lidas || 0) + "\n";
                textoResultado += 'Códigos identificados: ' + (importacao.codigos_identificados || 0) + "\n";
                textoResultado += 'Custos atualizados: ' + (importacao.custos_atualizados || 0) + "\n";
                textoResultado += 'Não encontrados: ' + (importacao.total_nao_encontrados || 0) + "\n\n";

                textoResultado += 'Aplicação na ficha:' + "\n";
                textoResultado += 'Itens analisados: ' + (aplicacao.itens_analisados || 0) + "\n";
                textoResultado += 'Itens atualizados: ' + (aplicacao.itens_atualizados || 0) + "\n";
                textoResultado += 'Itens sem custo: ' + (aplicacao.total_sem_custo || 0) + "\n\n";

                textoResultado += 'Consolidação:' + "\n";
                textoResultado += 'Registros consolidados: ' + (estrutura.registros_consolidados || 0) + "\n";
                textoResultado += 'Custo MP total: ' + numeroParaMoeda(estrutura.custo_mp_total || 0, 2) + "\n";
                textoResultado += 'Custo total: ' + numeroParaMoeda(estrutura.custo_total || 0, 2) + "\n";

                if (importacao.nao_encontrados && importacao.nao_encontrados.length > 0) {
                    textoResultado += "\nNão encontrados:" + "\n";

                    $.each(importacao.nao_encontrados, function (index, item) {
                        textoResultado += '- ' + item.cod_mp + ' | ' + item.desc_mp + "\n";
                    });
                }

                $('#resultadoImportacaoCustosTexto').text(textoResultado);

                $('#ano').val(ano);
                $('#mes').val(mes);

                carregarCustosMp();
            } else {
                $('#resultadoImportacaoCustosTexto').text(response.message || 'Erro ao importar custos.');
            }
        },
        error: function (xhr) {
            var mensagem = 'Erro ao importar custos manuais.';

            if (xhr.responseJSON && xhr.responseJSON.erro) {
                mensagem = xhr.responseJSON.erro;
            }

            $('#resultadoImportacaoCustosTexto').text(mensagem);
        },
        complete: function () {
            $btn.prop('disabled', false);
            $btn.html('<i class="fas fa-cogs"></i> Processar importação');
        }
    });
});
</script>

@endsection
