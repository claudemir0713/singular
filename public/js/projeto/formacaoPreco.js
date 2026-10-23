$(document).ready(function () {
    iniciarFormacaoPreco();
});

function iniciarFormacaoPreco() {
    aplicarMascaraMoeda();
    configurarEventosProduto();
    configurarEventosParametros();
    configurarEventosFormaCalculo();
    configurarModalDescontoPreco();
    configurarModalEstruturaProduto();
    configurarModalMemoriaCalculo();
    atualizarEstadoFormaCalculo(false);
    atualizarPrecoVenda();
}

function atualizarEstadoFormaCalculo(focarCampo) {
    focarCampo = focarCampo === true;

    var calculoPorPreco = $('#formaCalculo2').is(':checked');

    if (calculoPorPreco) {
        $('#forma_calculo').val('1');

        $('#margem')
            .prop('disabled', true)
            .addClass('campo-desabilitado');

        $('#margem1')
            .prop('disabled', false)
            .removeClass('campo-desabilitado');

        if (focarCampo) {
            $('#margem1').focus().select();
        }
    } else {
        $('#forma_calculo').val('0');

        $('#margem')
            .prop('disabled', false)
            .removeClass('campo-desabilitado');

        $('#margem1')
            .prop('disabled', true)
            .addClass('campo-desabilitado');
    }
}

function configurarEventosProduto() {
    $(document).off('click.formacaoProduto', '#btn_buscar_produto');
    $(document).on('click.formacaoProduto', '#btn_buscar_produto', function () {
        buscarCustoProduto($('#codigo_produto').val());
    });

    $(document).off('keypress.formacaoProduto', '#codigo_produto');
    $(document).on('keypress.formacaoProduto', '#codigo_produto', function (e) {
        if (e.which === 13 || e.keyCode === 13) {
            e.preventDefault();
            buscarCustoProduto($('#codigo_produto').val());
        }
    });

    $(document).off(
        'keyup.formacaoCustos change.formacaoCustos blur.formacaoCustos',
        '.atualiza_pv'
    );

    $(document).on(
        'keyup.formacaoCustos change.formacaoCustos blur.formacaoCustos',
        '.atualiza_pv',
        function () {
            atualizarPrecoVenda();
        }
    );

    $(document).off('click.formacaoSelecionarProduto', '.produto-resultado');
    $(document).on('click.formacaoSelecionarProduto', '.produto-resultado', function () {
        var codigo = String($(this).data('codigo') || '').trim();
        var descricao = String($(this).data('descricao') || '').trim();

        if (codigo === '') {
            return;
        }

        $('#codigo_produto').val(codigo);

        $('#label_produto_codigo').text(codigo);
        $('#label_produto_descricao').text(descricao);

        $('#descricao_produto').val(descricao);

        $('#ModalProdutos').modal('hide');
        $('#modal_lista_produtos').empty();

        buscarCustoProduto(codigo);
    });

    $(document).off(
        'input.formacaoFiltrarProdutos',
        '#modal_pesquisa_produto'
    );

    $(document).on(
        'input.formacaoFiltrarProdutos',
        '#modal_pesquisa_produto',
        function () {
            var pesquisa = normalizarTextoProduto($(this).val());

            pesquisa = pesquisa
                .replace(/[%*]+/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();

            var termos = pesquisa === ''
                ? []
                : pesquisa.split(' ');

            var encontrados = 0;

            $('.produto-resultado').each(function () {
                var item = $(this);
                var texto = normalizarTextoProduto(
                    item.attr('data-pesquisa') || item.text()
                );

                var corresponde = termos.every(function (termo) {
                    return texto.indexOf(termo) !== -1;
                });

                if (corresponde) {
                    item.show();
                    encontrados++;
                } else {
                    item.hide();
                }
            });

            $('#modal_sem_resultados').toggle(
                termos.length > 0 && encontrados === 0
            );

            $('#quantidade_produtos_encontrados').text(
                encontrados + ' produto(s) encontrado(s).'
            );
        }
    );


}

function configurarEventosFormaCalculo() {
    $(document).off('change.formacaoCalculo', '#formaCalculo1, #formaCalculo2');
    $(document).on('change.formacaoCalculo', '#formaCalculo1, #formaCalculo2', function () {
        atualizarEstadoFormaCalculo(true);
        atualizarPrecoVenda();
    });

    var formaInicial = $('#forma_calculo').val() || '0';

    if (formaInicial === '1') {
        $('#formaCalculo2').prop('checked', true);
        $('#formaCalculo1').prop('checked', false);
        $('#forma_calculo').val('1');
    } else {
        $('#formaCalculo1').prop('checked', true);
        $('#formaCalculo2').prop('checked', false);
        $('#forma_calculo').val('0');
    }
}

function configurarEventosParametros() {
    $(document).off('show.bs.modal.formacaoParametros', '#ModalParamentros');
    $(document).on('show.bs.modal.formacaoParametros', '#ModalParamentros', function () {
        carregarModalParametros();
    });

    $(document).off('click.formacaoParametrosSimular', '#md_simular');
    $(document).on('click.formacaoParametrosSimular', '#md_simular', function () {
        aplicarModalParametros();
        $('#ModalParamentros').modal('hide');
    });

    $(document).off('click.formacaoParametrosGravar', '#md_grava');
    $(document).on('click.formacaoParametrosGravar', '#md_grava', function () {
        aplicarModalParametros();
        gravarParametrosPadrao();
    });

    $(document).off('click.formacaoVoltar', '#btn_voltar');
    $(document).on('click.formacaoVoltar', '#btn_voltar', function () {
        window.history.back();
    });

    $(document).off('click.formacaoSalvar', '#salvar_preco');
    $(document).on('click.formacaoSalvar', '#salvar_preco', function () {
        salvarFormacaoPreco();
    });
}

function buscarCustoProduto(codigo) {
    codigo = $.trim(codigo || '');

    if (codigo === '') {
        limparDadosProduto();
        mostrarMensagemFormacaoPreco('Informe o código do produto.', 'warning');
        return;
    }

    var urlConsulta = $('#url_buscar_custo_produto').val();

    if (!urlConsulta) {
        mostrarMensagemFormacaoPreco('URL de consulta de custo não encontrada na tela.', 'danger');
        return;
    }

    $.ajax({
        url: urlConsulta,
        type: 'GET',
        dataType: 'json',
        data: {
            codigo: codigo
        },
        beforeSend: function () {
            mostrarMensagemFormacaoPreco('Buscando custo do produto...', 'info');

            $('#btn_buscar_produto')
                .prop('disabled', true)
                .html('<span class="fas fa-spinner fa-spin"></span> <span>Buscando...</span>');
        },
        success: function (response) {
            if (response && response.selecionar_produto === true) {
                montarListaProdutos(response.produtos || []);

                mostrarMensagemFormacaoPreco(
                    response.message || 'Selecione o produto desejado.',
                    'info'
                );

                return;
            }

            if (!response || response.success !== true || !response.data) {
                limparDadosProduto();
                mostrarMensagemFormacaoPreco('Não foi possível buscar o custo do produto.', 'danger');
                atualizarPrecoVenda();
                return;
            }

            preencherDadosCustoProduto(response.data, codigo);

            if (response.data.encontrado === true) {
                mostrarMensagemFormacaoPreco(response.message || 'Custo localizado com sucesso.', 'success');

                $('#resumo_status').text(
                    formatarTipoCusto(response.data.tipo_item_custo) +
                    ' localizado em ' +
                    (response.data.origem_custo || '') +
                    '.'
                );
            } else {
                mostrarMensagemFormacaoPreco(response.message || 'Custo não encontrado para o item informado.', 'warning');
                $('#resumo_status').text('Custo não encontrado para o item informado.');
            }

            atualizarPrecoVenda();
        },
        error: function (xhr) {
            limparDadosProduto();

            var mensagem = 'Erro ao consultar custo do produto.';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensagem = xhr.responseJSON.message;
            }

            mostrarMensagemFormacaoPreco(mensagem, 'danger');
            atualizarPrecoVenda();
        },
        complete: function () {
            $('#btn_buscar_produto')
                .prop('disabled', false)
                .html('<span class="fas fa-search"></span> <span>Buscar</span>');
        }
    });
}

function montarListaProdutos(produtos) {
    var lista = $('#modal_lista_produtos');

    lista.empty();
    $('#modal_pesquisa_produto').val('');
    $('#modal_sem_resultados').hide();

    if (!produtos || produtos.length === 0) {
        lista.html(
            '<div class="alert alert-warning fonte-10 mb-0">' +
            'Nenhum produto encontrado.' +
            '</div>'
        );

        $('#ModalProdutos').modal('show');
        return;
    }

    $.each(produtos, function (indice, produto) {
        var codigo = String(produto.cod || '');
        var descricao = String(produto.descr || '');

        var item = $('<button>', {
            type: 'button',
            class: 'list-group-item list-group-item-action produto-resultado'
        });

        item.attr('data-codigo', codigo);
        item.attr('data-descricao', descricao);

        item.attr(
            'data-pesquisa',
            normalizarTextoProduto(codigo + ' ' + descricao)
        );

        item.append(
            $('<strong>').text(codigo)
        );

        item.append(
            document.createTextNode(' - ' + descricao)
        );

        lista.append(item);
    });

    $('#ModalProdutos').modal('show');
}

function normalizarTextoProduto(texto) {
    texto = String(texto || '').toLowerCase();

    if (typeof texto.normalize === 'function') {
        texto = texto
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    return texto.trim();
}

function preencherDadosCustoProduto(data, codigoDigitado) {
    var codigo = data.codigo || codigoDigitado || '';

    var descricao = data.descricao
        || data.descr
        || $('#descricao_produto').val()
        || '';

    var custoMateriais = parseBRNumber(data.custo_mp_unit || 0);
    var custoMoi = parseBRNumber(data.custo_op_unit || 0);

    var custoPercentual = data.encontrado === true
        ? parseBRNumber(data.custo_percentual || 0)
        : 0;

    $('#label_produto_codigo').text(codigo || 'Não informado');
    $('#label_produto_descricao').text(descricao || '');

    $('#codigo_resultado').val(codigo);
    $('#descricao_produto').val(descricao);

    $('#tipo_item_custo').val(data.tipo_item_custo || '');
    $('#origem_custo').val(data.origem_custo || '');

    $('#ano_custo').val(data.ano || '');
    $('#mes_custo').val(data.mes || '');
    $('#ano_mes_custo').val(data.ano_mes_custo || '');

    $('#embalagem').val(data.embalagem || '');
    $('#custo_percentual').val(formatPercentNumber(custoPercentual));
    $('#unid_med').val(data.unid_med || '');
    $('#tipo_mp').val(data.tipo_mp || '');

    $('#custo_materiais').val(formatCurrencyNumber(custoMateriais));
    $('#custo_moi').val(formatCurrencyNumber(custoMoi));
    $('#custo_moe').val(formatCurrencyNumber(0));
    $('#vl_frete').val(formatCurrencyNumber(0));

    $('#btn_toggle_dados_custos').html(
        '<i class="fas fa-box-open"></i> Ver dados e custos do produto: ' + codigo
    );
    preencherEstruturaProduto(data.estrutura, codigo);
}
/************card estrutura*******************************************/
function preencherEstruturaProduto(estrutura, codigo) {
    limparEstruturaProduto();

    if (!estrutura || estrutura.encontrado !== true) {
        return;
    }

    var materiasPrimas = estrutura.materias_primas || [];
    var operacionais = estrutura.operacionais || [];

    preencherEstruturaMateriasPrimas(materiasPrimas);
    preencherEstruturaOperacional(operacionais);

    $('#estrutura_total_mp').text(
        'Total: ' + formatCurrencyNumber(estrutura.total_materia_prima || 0)
    );

    $('#estrutura_total_op').text(
        'Total: ' + formatCurrencyNumber(estrutura.total_operacional || 0)
    );

    var anoMes = estrutura.ano_mes || '';

    $('#btn_toggle_estrutura_produto')
        .html(
            '<i class="fas fa-sitemap"></i> ' +
            'Ver estrutura do produto: ' + codigo +
            (anoMes !== '' ? ' - ' + anoMes : '')
        )
        .show();

}

function preencherEstruturaMateriasPrimas(itens) {
    var tbody = $('#estrutura_lista_mp');

    tbody.empty();

    if (!itens || itens.length === 0) {
        tbody.html(
            '<tr>' +
            '<td colspan="6" class="text-center text-muted">' +
            'Nenhuma matéria-prima localizada.' +
            '</td>' +
            '</tr>'
        );

        return;
    }

    $.each(itens, function (indice, item) {
        var linha = $('<tr>');

        linha.append(
            $('<td>').text(item.cod_comp || '')
        );

        linha.append(
            $('<td>').text(item.desc_comp || '')
        );

        linha.append(
            $('<td>')
                .addClass('text-center')
                .text(item.nivel || 0)
        );

        linha.append(
            $('<td>')
                .addClass('text-right')
                .text(formatarNumeroEstrutura(item.qtde_calc, 6))
        );

        linha.append(
            $('<td>')
                .addClass('text-right')
                .text(formatarNumeroEstrutura(item.custo_mp_unit, 6))
        );

        linha.append(
            $('<td>')
                .addClass('text-right font-weight-bold')
                .text(formatCurrencyNumber(item.valor_mp || 0))
        );

        tbody.append(linha);
    });
}

function preencherEstruturaOperacional(itens) {
    var tbody = $('#estrutura_lista_op');

    tbody.empty();

    if (!itens || itens.length === 0) {
        tbody.html(
            '<tr>' +
            '<td colspan="6" class="text-center text-muted">' +
            'Nenhum custo operacional localizado.' +
            '</td>' +
            '</tr>'
        );

        return;
    }

    $.each(itens, function (indice, item) {
        var linha = $('<tr>');

        linha.append(
            $('<td>').text(item.cod_comp || '')
        );

        linha.append(
            $('<td>').text(item.desc_comp || '')
        );

        linha.append(
            $('<td>')
                .addClass('text-center')
                .text(item.nivel || 0)
        );

        linha.append(
            $('<td>')
                .addClass('text-right')
                .text(formatarNumeroEstrutura(item.base_op_qtd, 6))
        );

        linha.append(
            $('<td>')
                .addClass('text-right')
                .text(formatarNumeroEstrutura(item.custo_op_unit, 6))
        );

        linha.append(
            $('<td>')
                .addClass('text-right font-weight-bold')
                .text(formatCurrencyNumber(item.valor_op || 0))
        );

        tbody.append(linha);
    });
}

function formatarNumeroEstrutura(valor, casas) {
    valor = parseFloat(valor) || 0;
    casas = casas || 2;

    return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: casas
    }).format(valor);
}

function limparEstruturaProduto() {
    $('#collapseEstruturaProduto').collapse('hide');

    $('#btn_toggle_estrutura_produto')
        .hide()
        .html(
            '<i class="fas fa-sitemap"></i> ' +
            'Ver estrutura do produto'
        );

    $('#estrutura_total_mp').text('Total: 0,00');
    $('#estrutura_total_op').text('Total: 0,00');

    $('#estrutura_lista_mp').html(
        '<tr>' +
        '<td colspan="6" class="text-center text-muted">' +
        'Nenhuma matéria-prima localizada.' +
        '</td>' +
        '</tr>'
    );

    $('#estrutura_lista_op').html(
        '<tr>' +
        '<td colspan="6" class="text-center text-muted">' +
        'Nenhum custo operacional localizado.' +
        '</td>' +
        '</tr>'
    );
}

/**************************************************************************** */

function limparDadosProduto() {
    limparEstruturaProduto();
    $('#label_produto_codigo').text('Não informado');
    $('#label_produto_descricao').text('');

    $('#codigo_resultado').val('');
    $('#descricao_produto').val('');
    $('#tipo_item_custo').val('');
    $('#origem_custo').val('');

    $('#ano_custo').val('');
    $('#mes_custo').val('');
    $('#ano_mes_custo').val('');

    $('#embalagem').val('');
    $('#custo_percentual').val('');
    $('#unid_med').val('');
    $('#tipo_mp').val('');

    $('#custo_materiais').val(formatCurrencyNumber(0));
    $('#custo_moi').val(formatCurrencyNumber(0));
    $('#custo_moe').val(formatCurrencyNumber(0));
    $('#vl_frete').val(formatCurrencyNumber(0));

    $('#vlr_custo_material_operacional').text(formatCurrencyNumber(0));
    $('#prec_custo_material_operacional').text(formatPercentNumber(0));

    $('#resumo_status').text('Aguardando consulta do produto.');

    $('#btn_toggle_dados_custos').html(
        '<i class="fas fa-box-open"></i> Ver dados e custos do produto'
    );
}

function carregarModalParametros() {
    $('#md_margem').val($('#margem').val());
    $('#md_comissao').val($('#comissao').val());

    $('#md_parametro_taxa_financeira').val($('#parametro_taxa_financeira').val());
    $('#md_parametro_icms').val($('#parametro_icms').val());
    $('#md_parametro_simples').val($('#parametro_simples').val());
    $('#md_parametro_pis').val($('#parametro_pis').val());
    $('#md_parametro_cofins').val($('#parametro_cofins').val());
    $('#md_parametro_ir_csll').val($('#parametro_ir_csll').val());
    $('#md_parametro_difal').val($('#parametro_difal').val());
    $('#md_parametro_frete_percentual').val($('#parametro_frete_percentual').val());
    $('#md_parametro_despesa_fixa').val($('#parametro_despesa_fixa').val());
    $('#md_parametro_outros').val($('#parametro_outros').val());
}

function aplicarModalParametros() {
    $('#margem').val($('#md_margem').val());
    $('#comissao').val($('#md_comissao').val());

    $('#parametro_taxa_financeira').val($('#md_parametro_taxa_financeira').val());
    $('#parametro_icms').val($('#md_parametro_icms').val());
    $('#parametro_simples').val($('#md_parametro_simples').val());
    $('#parametro_pis').val($('#md_parametro_pis').val());
    $('#parametro_cofins').val($('#md_parametro_cofins').val());
    $('#parametro_ir_csll').val($('#md_parametro_ir_csll').val());
    $('#parametro_difal').val($('#md_parametro_difal').val());
    $('#parametro_frete_percentual').val($('#md_parametro_frete_percentual').val());
    $('#parametro_despesa_fixa').val($('#md_parametro_despesa_fixa').val());
    $('#parametro_outros').val($('#md_parametro_outros').val());

    atualizarPrecoVenda();
}

function gravarParametrosPadrao() {
    var url = window.location.origin + '/singular/public/formacaoPreco/gravar-parametros-padrao';
    var botao = $('#md_grava');
    var textoOriginal = botao.html();

    function exibirSwal(tipo, titulo, mensagem) {
        if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
            Swal.fire({
                title: titulo,
                text: mensagem,
                type: tipo,
                timer: tipo === 'success' ? 1000 : undefined,
                confirmButtonText: 'OK'
            });

            return;
        }

        if (typeof swal !== 'undefined') {
            swal({
                title: titulo,
                text: mensagem,
                type: tipo,
                button: 'OK'
            });

            return;
        }

        alert(mensagem);
    }

    botao
        .prop('disabled', true)
        .html('<span class="fas fa-spinner fa-spin"></span> Gravando...');

    $.ajax({
        url: url,
        type: 'POST',
        dataType: 'json',
        data: $('#form_modal_parametros').serialize(),

        success: function (response) {
            if (response && response.success === true) {
                aplicarModalParametros();
                $('#ModalParamentros').modal('hide');

                exibirSwal(
                    'success',
                    'Sucesso!',
                    response.message || 'Parâmetros padrão gravados com sucesso.'
                );

                return;
            }

            exibirSwal(
                'warning',
                'Atenção!',
                response && response.message
                    ? response.message
                    : 'Não foi possível gravar os parâmetros padrão.'
            );
        },

        error: function (xhr) {
            console.log('STATUS:', xhr.status);
            console.log('URL:', url);
            console.log('RESPONSE:', xhr.responseText);

            var mensagem = 'Erro ao gravar parâmetros padrão.';

            if (xhr.status === 404) {
                mensagem = 'Rota para gravação dos parâmetros não encontrada.';
            } else if (xhr.status === 419) {
                mensagem = 'A sessão expirou. Atualize a página e tente novamente.';
            } else if (
                xhr.responseJSON &&
                xhr.responseJSON.message
            ) {
                mensagem = xhr.responseJSON.message;
            }

            exibirSwal(
                'error',
                'Erro!',
                mensagem
            );
        },

        complete: function () {
            botao
                .prop('disabled', false)
                .html(textoOriginal);
        }
    });
}

function precoVendaCalcular(params) {
    var _custo = parseBRNumber(params.custo);
    var _imposto_custo = parseBRNumber(params.imposto_custo) / 100;
    var _imposto_venda = parseBRNumber(params.imposto_venda) / 100;
    var _difal = parseBRNumber(params.difal) / 100;
    var _ir_csll = parseBRNumber(params.ir_csll) / 100;
    var _outros = parseBRNumber(params.outros) / 100;
    var _comissao = parseBRNumber(params.comissao) / 100;
    var _frete = parseBRNumber(params.frete) / 100;
    var _despesa_fixa = parseBRNumber(params.despesa_fixa) / 100;
    var _margem = parseBRNumber(params.margem) / 100;
    var _qtd = parseBRNumber(params.qtd);
    var _prazoMedio = parseBRNumber(params.prazoMedio);
    var _taxa_financeira = parseBRNumber(params.taxa_financeira);

    var equivalencia = Math.pow((_taxa_financeira / 100 + 1), (_prazoMedio / 30));

    if (isNaN(equivalencia) || equivalencia <= 0) {
        equivalencia = 1;
    }

    _custo = _custo * ((_imposto_custo - 1) * -1);

    var custoUnitario = _qtd > 0 ? _custo / _qtd : 0;
    var valorVendaUnitario = 0;
    var valorVendaTotal = 0;
    var markupIrCsll = 1 - (_ir_csll * equivalencia);

    if (markupIrCsll <= 0) {
        markupIrCsll = 1;
    }

    var markup = (
        ((1 - (_margem / markupIrCsll)) / equivalencia)
        - _imposto_venda
        - _frete
        - _comissao
        - _despesa_fixa
        - _difal
        - _outros
    );

    if (markup > 0) {
        valorVendaUnitario = custoUnitario / markup;
        valorVendaTotal = valorVendaUnitario * _qtd;
    }

    return {
        equivalencia: equivalencia,
        custoAjustado: _custo,
        custoUnitario: custoUnitario,
        vlrVendaTotal: valorVendaTotal,
        vlrVendaUnitario: valorVendaUnitario,
        markup: markup
    };
}

function montarResultadoPorPrecoInformado(precoVenda, custoTotal) {
    var prazo = parseBRNumber($('#prazo').val());
    var taxaFinanceira = parseBRNumber($('#parametro_taxa_financeira').val());
    var equivalencia = Math.pow((taxaFinanceira / 100 + 1), (prazo / 30));

    if (isNaN(equivalencia) || equivalencia <= 0) {
        equivalencia = 1;
    }

    return {
        equivalencia: equivalencia,
        custoAjustado: custoTotal,
        custoUnitario: custoTotal,
        vlrVendaTotal: precoVenda,
        vlrVendaUnitario: precoVenda,
        markup: 0
    };
}

function atualizarPrecoVenda() {
    atualizarEstadoFormaCalculo(false);

    var custoMateriais = parseBRNumber($('#custo_materiais').val());
    var custoMoi = parseBRNumber($('#custo_moi').val());
    var custoMoe = parseBRNumber($('#custo_moe').val());
    var freteValor = parseBRNumber($('#vl_frete').val());

    var custoTotal = custoMateriais + custoMoi + custoMoe + freteValor;

    var icms = parseBRNumber($('#parametro_icms').val());
    var simples = parseBRNumber($('#parametro_simples').val());
    var pis = parseBRNumber($('#parametro_pis').val());
    var cofins = parseBRNumber($('#parametro_cofins').val());
    var impostoVenda = icms + simples + pis + cofins;

    var calculoPorPreco = $('#formaCalculo2').is(':checked');
    var resultado;
    var precoVenda = 0;

    if (calculoPorPreco) {
        precoVenda = parseBRNumber($('#margem1').val());
        resultado = montarResultadoPorPrecoInformado(precoVenda, custoTotal);
    } else {
        resultado = precoVendaCalcular({
            custo: custoTotal,
            imposto_custo: 0,
            imposto_venda: impostoVenda,
            difal: $('#parametro_difal').val(),
            ir_csll: $('#parametro_ir_csll').val(),
            outros: $('#parametro_outros').val(),
            comissao: $('#comissao').val(),
            frete: $('#parametro_frete_percentual').val(),
            despesa_fixa: $('#parametro_despesa_fixa').val(),
            margem: $('#margem').val(),
            qtd: 1,
            prazoMedio: $('#prazo').val(),
            taxa_financeira: $('#parametro_taxa_financeira').val()
        });

        precoVenda = resultado.vlrVendaTotal;
        $('#margem1').val(formatCurrencyNumber(precoVenda));
    }

    $('#vlr_venda').text(formatCurrencyNumber(precoVenda));
    $('#preco_venda_calculado').val(formatCurrencyNumber(precoVenda));
    $('#preco_venda').val(formatCurrencyNumber(precoVenda));
    $('#vlr_custo_material_operacional').text(formatCurrencyNumber(custoTotal));

    var percentualCusto = precoVenda > 0 ? (custoTotal / precoVenda) * 100 : 0;
    $('#prec_custo_material_operacional').text(formatPercentNumber(percentualCusto));

    calcularMemoriaPreco(precoVenda, custoTotal);

    return resultado;
}

function calcularMemoriaPreco(precoVenda, custoTotal) {
    var prazo = parseBRNumber($('#prazo').val());
    var taxaFinanceira = parseBRNumber($('#parametro_taxa_financeira').val());

    var icms = parseBRNumber($('#parametro_icms').val());
    var simples = parseBRNumber($('#parametro_simples').val());
    var pis = parseBRNumber($('#parametro_pis').val());
    var cofins = parseBRNumber($('#parametro_cofins').val());

    var difal = parseBRNumber($('#parametro_difal').val());
    var fretePercent = parseBRNumber($('#parametro_frete_percentual').val());
    var outros = parseBRNumber($('#parametro_outros').val());
    var comissao = parseBRNumber($('#comissao').val());

    var despesaFixa = parseBRNumber($('#parametro_despesa_fixa').val());
    var irCsll = parseBRNumber($('#parametro_ir_csll').val());

    var percentualImpostos = icms + simples + pis + cofins;
    var percentualOutrasVariaveis = difal + fretePercent + outros + comissao;

    var taxaFinanceiraEfetiva = (Math.pow((taxaFinanceira / 100) + 1, prazo / 30) - 1) * 100;

    if (isNaN(taxaFinanceiraEfetiva) || taxaFinanceiraEfetiva < 0) {
        taxaFinanceiraEfetiva = 0;
    }

    var valorImpostos = precoVenda > 0 ? precoVenda * (percentualImpostos / 100) : 0;
    var valorOutrasVariaveis = precoVenda > 0 ? precoVenda * (percentualOutrasVariaveis / 100) : 0;

    var baseCustoFinanceiro = custoTotal + valorImpostos + valorOutrasVariaveis;
    var valorCustoFinanceiro = precoVenda > 0 ? baseCustoFinanceiro * (taxaFinanceiraEfetiva / 100) : 0;

    var margemContribuicao = precoVenda - custoTotal - valorImpostos - valorOutrasVariaveis - valorCustoFinanceiro;
    var percentualMargemContribuicao = precoVenda > 0 ? (margemContribuicao / precoVenda) * 100 : 0;

    var valorDespesaFixa = precoVenda > 0 ? precoVenda * (despesaFixa / 100) : 0;
    var valorFinanceiroDespesaFixa = valorDespesaFixa * (taxaFinanceiraEfetiva / 100);

    var resultadoBruto = margemContribuicao - valorDespesaFixa - valorFinanceiroDespesaFixa;
    var percentualResultadoBruto = precoVenda > 0 ? (resultadoBruto / precoVenda) * 100 : 0;

    var valorIrCsll = resultadoBruto > 0 ? resultadoBruto * (irCsll / 100) : 0;
    var valorFinanceiroIrCsll = valorIrCsll * (taxaFinanceiraEfetiva / 100);

    var resultadoLiquido = resultadoBruto - valorIrCsll - valorFinanceiroIrCsll;
    var percentualResultadoLiquido = precoVenda > 0 ? (resultadoLiquido / precoVenda) * 100 : 0;

    $('#vl_impostos').text(formatCurrencyNumber(valorImpostos));
    $('#perc_impostos').text(formatPercentNumber(percentualImpostos));

    $('#vl_outras_variaveis').text(formatCurrencyNumber(valorOutrasVariaveis));
    $('#perc_outras_variaveis').text(formatPercentNumber(percentualOutrasVariaveis));

    $('#vl_custo_financeiro_outras_variaveis').text(formatCurrencyNumber(valorCustoFinanceiro));

    $('#vlr_mc').text(formatCurrencyNumber(margemContribuicao));
    $('#prec_mc').text(formatPercentNumber(percentualMargemContribuicao));

    $('#vl_despesas_fixas').text(formatCurrencyNumber(valorDespesaFixa));
    $('#vl_financeiro_despesas_fixas').text(formatCurrencyNumber(valorFinanceiroDespesaFixa));

    $('#vlr_rb').text(formatCurrencyNumber(resultadoBruto));
    $('#prec_rb').text(formatPercentNumber(percentualResultadoBruto));

    $('#vl_ir_csl').text(formatCurrencyNumber(valorIrCsll));
    $('#vl_financeiro_ir_csl').text(formatCurrencyNumber(valorFinanceiroIrCsll));

    $('#vlr_liquido').text(formatCurrencyNumber(resultadoLiquido));
    $('#prec_liquido').text(formatPercentNumber(percentualResultadoLiquido));

    atualizarCardsResumo(precoVenda, margemContribuicao, percentualMargemContribuicao, resultadoLiquido, percentualResultadoLiquido);
}

function aplicarClasseResumoCard(cardId, valor) {
    var card = $(cardId);
    card.removeClass('card-resumo-positivo card-resumo-negativo card-resumo-neutro');

    if (valor > 0) {
        card.addClass('card-resumo-positivo');
    } else if (valor < 0) {
        card.addClass('card-resumo-negativo');
    } else {
        card.addClass('card-resumo-neutro');
    }
}

function atualizarCardsResumo(precoVenda, margemContribuicao, percentualMargemContribuicao, resultadoLiquido, percentualResultadoLiquido) {
    $('#card_valor_venda').text(formatCurrencyNumber(precoVenda));

    $('#card_mc_valor').text(formatCurrencyNumber(margemContribuicao));
    $('#card_mc_percentual').text(formatPercentNumber(percentualMargemContribuicao) + '%');

    $('#card_ml_valor').text(formatCurrencyNumber(resultadoLiquido));
    $('#card_ml_percentual').text(formatPercentNumber(percentualResultadoLiquido) + '%');

    aplicarClasseResumoCard('#card_mc_box', margemContribuicao);
    aplicarClasseResumoCard('#card_ml_box', resultadoLiquido);
}

function salvarFormacaoPreco() {
    atualizarPrecoVenda();

    var urlSalvar = $('#url_salvar_formacao_preco').val();

    if (!urlSalvar) {
        alert('URL para salvar formação de preço não encontrada.');
        return;
    }

    var dados = coletarDadosFormacaoPreco();

    if (!dados.cod_produto) {
        alert('Informe e busque um produto antes de salvar.');
        return;
    }

    if (parseBRNumber(dados.custo_total) <= 0) {
        alert('Não é possível salvar uma formação sem custo.');
        return;
    }

    $.ajax({
        url: urlSalvar,
        type: 'POST',
        dataType: 'json',
        data: dados,
        beforeSend: function () {
            $('#salvar_preco')
                .prop('disabled', true)
                .html('<span class="fas fa-spinner fa-spin"></span> Salvando...');
        },
        success: function (response) {
            if (response && response.success === true) {
                alert(response.message || 'Formação de preço salva com sucesso.');
                return;
            }

            alert(response.message || 'Não foi possível salvar a formação de preço.');
        },
        error: function (xhr) {
            var mensagem = 'Erro ao salvar formação de preço.';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                mensagem = xhr.responseJSON.message;
            }

            alert(mensagem);
        },
        complete: function () {
            $('#salvar_preco')
                .prop('disabled', false)
                .html('<span class="fas fa-save"></span> <span>Salvar</span>');
        }
    });
}

function coletarDadosFormacaoPreco() {
    return {
        _token: $('input[name="_token"]').val(),

        cod_produto: $('#codigo_resultado').val(),
        descricao_produto: $('#descricao_produto').val(),
        tipo_item_custo: $('#tipo_item_custo').val(),
        origem_custo: $('#origem_custo').val(),

        ano_custo: parseInt(parseBRNumber($('#ano_custo').val())) || null,
        mes_custo: parseInt(parseBRNumber($('#mes_custo').val())) || null,
        ano_mes_custo: $('#ano_mes_custo').val(),

        embalagem: $('#embalagem').val(),
        custo_percentual: parseBRNumber($('#custo_percentual').val()),
        unid_med: $('#unid_med').val(),
        tipo_mp: $('#tipo_mp').val(),

        custo_materiais: parseBRNumber($('#custo_materiais').val()),
        custo_moi: parseBRNumber($('#custo_moi').val()),
        custo_moe: parseBRNumber($('#custo_moe').val()),
        vl_frete: parseBRNumber($('#vl_frete').val()),
        custo_total: parseBRNumber($('#vlr_custo_material_operacional').text()),

        prazo: parseInt(parseBRNumber($('#prazo').val())) || 0,
        forma_calculo: parseInt($('#forma_calculo').val()) || 0,

        margem: parseBRNumber($('#margem').val()),
        preco_informado: parseBRNumber($('#margem1').val()),
        comissao: parseBRNumber($('#comissao').val()),

        taxa_financeira: parseBRNumber($('#parametro_taxa_financeira').val()),
        icms: parseBRNumber($('#parametro_icms').val()),
        simples: parseBRNumber($('#parametro_simples').val()),
        pis: parseBRNumber($('#parametro_pis').val()),
        cofins: parseBRNumber($('#parametro_cofins').val()),
        ir_csll: parseBRNumber($('#parametro_ir_csll').val()),
        difal: parseBRNumber($('#parametro_difal').val()),
        frete_percentual: parseBRNumber($('#parametro_frete_percentual').val()),
        despesa_fixa: parseBRNumber($('#parametro_despesa_fixa').val()),
        outros: parseBRNumber($('#parametro_outros').val()),

        valor_venda: parseBRNumber($('#preco_venda_calculado').val()),
        valor_impostos: parseBRNumber($('#vl_impostos').text()),
        valor_outras_variaveis: parseBRNumber($('#vl_outras_variaveis').text()),
        valor_custo_financeiro: parseBRNumber($('#vl_custo_financeiro_outras_variaveis').text()),

        valor_margem_contribuicao: parseBRNumber($('#vlr_mc').text()),
        percentual_margem_contribuicao: parseBRNumber($('#prec_mc').text()),

        valor_resultado_bruto: parseBRNumber($('#vlr_rb').text()),
        percentual_resultado_bruto: parseBRNumber($('#prec_rb').text()),

        valor_lucro: parseBRNumber($('#vlr_liquido').text()),
        percentual_lucro: parseBRNumber($('#prec_liquido').text())
    };
}

function parseBRNumber(value) {
    if (value === null || value === undefined || value === '') {
        return 0;
    }

    if (typeof value === 'number') {
        return value;
    }

    var valor = String(value).trim().replace(/\./g, '').replace(',', '.');
    var numero = parseFloat(valor);

    return isNaN(numero) ? 0 : numero;
}

function formatCurrencyNumber(num) {
    var valor = parseFloat(num) || 0;

    return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(valor);
}

function formatPercentNumber(num) {
    var valor = parseFloat(num) || 0;

    return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(valor);
}

function somenteNumeros(value) {
    return String(value || '').replace(/\D/g, '');
}

function formatarMoedaDigitacao(value) {
    var numeros = somenteNumeros(value);

    if (numeros === '') {
        return '';
    }

    return formatCurrencyNumber(parseFloat(numeros) / 100);
}

function aplicarMascaraMoeda() {
    $('.mask-money').off('input.formacaoPrecoMask');
    $('.mask-money').on('input.formacaoPrecoMask', function () {
        $(this).val(formatarMoedaDigitacao($(this).val()));
        atualizarPrecoVenda();
    });

    $('.mask-money').off('blur.formacaoPrecoMask');
    $('.mask-money').on('blur.formacaoPrecoMask', function () {
        var valor = parseBRNumber($(this).val());

        if (valor > 0) {
            $(this).val(formatCurrencyNumber(valor));
        } else {
            $(this).val('');
        }
    });
}

function mostrarMensagemFormacaoPreco(mensagem, tipo) {
    var classe = 'alert-info';

    if (tipo === 'success') {
        classe = 'alert-success';
    } else if (tipo === 'warning') {
        classe = 'alert-warning';
    } else if (tipo === 'danger') {
        classe = 'alert-danger';
    }

    $('#msg_formacao_preco').html(
        '<div class="alert ' + classe + ' fonte-10" style="padding: 6px 10px; margin-bottom: 0;">' +
        mensagem +
        '</div>'
    );
}

function formatarTipoCusto(tipo) {
    tipo = tipo || '';

    if (tipo === 'PRODUTO_ACABADO') {
        return 'Produto acabado';
    }

    if (tipo === 'MATERIA_PRIMA') {
        return 'Matéria-prima';
    }

    if (tipo === 'SEM_CUSTO') {
        return 'Sem custo';
    }

    return tipo;
}

function configurarModalDescontoPreco() {
    $(document).off('click.descontoPreco', '#btn_desconto_preco');
    $(document).on('click.descontoPreco', '#btn_desconto_preco', function () {
        abrirModalDescontoPreco();
    });

    $(document).off('change.descontoPreco', '#desconto_tipo');
    $(document).on('change.descontoPreco', '#desconto_tipo', function () {
        ajustarTipoDescontoPreco();
        calcularDescontoPreco();
    });

    $(document).off('keyup.descontoPreco change.descontoPreco blur.descontoPreco', '#desconto_percentual, #desconto_valor');
    $(document).on('keyup.descontoPreco change.descontoPreco blur.descontoPreco', '#desconto_percentual, #desconto_valor', function () {
        calcularDescontoPreco();
    });

    $(document).off('click.aplicarDescontoPreco', '#aplicar_desconto_preco');
    $(document).on('click.aplicarDescontoPreco', '#aplicar_desconto_preco', function () {
        aplicarDescontoPreco();
    });
}

/************desconto*****************************/

    function abrirModalDescontoPreco() {
        var precoBase = parseBRNumber($('#preco_venda_calculado').val());

        if (precoBase <= 0) {
            precoBase = parseBRNumber($('#card_valor_venda').text());
        }

        if (precoBase <= 0) {
            Swal.fire({
                type: 'warning',
                title: 'Atenção',
                text: 'Calcule o preço de venda antes de aplicar desconto.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#f0ad4e'
            });

            return;
        }

        $('#desconto_preco_base').val(formatCurrencyNumber(precoBase));
        $('#desconto_tipo').val('P');
        $('#desconto_percentual').val('');
        $('#desconto_valor').val('');
        $('#desconto_preco_final').val(formatCurrencyNumber(precoBase));

        ajustarTipoDescontoPreco();

        $('#ModalDescontoPreco').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#ModalDescontoPreco').modal('show');

        setTimeout(function () {
            $('#desconto_percentual').focus().select();
        }, 300);
    }

    function ajustarTipoDescontoPreco() {
        var tipo = $('#desconto_tipo').val();

        if (tipo === 'P') {
            $('#desconto_percentual').prop('readonly', false);
            $('#desconto_valor').prop('readonly', true);
        } else {
            $('#desconto_percentual').prop('readonly', true);
            $('#desconto_valor').prop('readonly', false);
        }
    }

    function calcularDescontoPreco() {
        var precoBase = parseBRNumber($('#desconto_preco_base').val());
        var tipo = $('#desconto_tipo').val();

        var percentualDesconto = parseBRNumber($('#desconto_percentual').val());
        var valorDesconto = parseBRNumber($('#desconto_valor').val());

        if (precoBase <= 0) {
            $('#desconto_valor').val(formatCurrencyNumber(0));
            $('#desconto_preco_final').val(formatCurrencyNumber(0));

            return {
                precoBase: 0,
                percentualDesconto: 0,
                valorDesconto: 0,
                precoFinal: 0
            };
        }

        if (tipo === 'P') {
            if (percentualDesconto < 0) {
                percentualDesconto = 0;
            }

            if (percentualDesconto > 100) {
                percentualDesconto = 100;
            }

            valorDesconto = precoBase * (percentualDesconto / 100);
            $('#desconto_valor').val(formatCurrencyNumber(valorDesconto));
        } else {
            if (valorDesconto < 0) {
                valorDesconto = 0;
            }

            if (valorDesconto > precoBase) {
                valorDesconto = precoBase;
            }

            percentualDesconto = precoBase > 0
                ? (valorDesconto / precoBase) * 100
                : 0;

            $('#desconto_percentual').val(formatPercentNumber(percentualDesconto));
        }

        var precoFinal = precoBase - valorDesconto;

        $('#desconto_preco_final').val(formatCurrencyNumber(precoFinal));

        return {
            precoBase: precoBase,
            percentualDesconto: percentualDesconto,
            valorDesconto: valorDesconto,
            precoFinal: precoFinal
        };
    }

    function aplicarDescontoPreco() {
        var resultado = calcularDescontoPreco();

        if (resultado.precoFinal <= 0) {
            Swal.fire({
                type: 'warning',
                title: 'Atenção',
                text: 'O preço final com desconto precisa ser maior que zero.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#f0ad4e'
            });

            return;
        }

        $('#formaCalculo2').prop('checked', true);
        $('#formaCalculo1').prop('checked', false);
        $('#forma_calculo').val('1');

        atualizarEstadoFormaCalculo(false);

        $('#margem1').val(formatCurrencyNumber(resultado.precoFinal));

        $('#ModalDescontoPreco').modal('hide');

        atualizarPrecoVenda();
    }

/************modal estrutura******************************/
    function configurarModalEstruturaProduto() {
        $(document).off('click.formacaoEstrutura', '#btn_ver_estrutura_produto');

        $(document).on('click.formacaoEstrutura', '#btn_ver_estrutura_produto', function () {
            abrirModalEstruturaProduto();
        });
    }

    function abrirModalEstruturaProduto() {
        var codigo = $('#codigo_resultado').val() || $('#codigo_produto').val() || '';
        var anoMes = $('#ano_mes_custo').val() || '';

        var html = $('#area_estrutura_produto').html();

        if (!html || $.trim(html) === '') {
            html = '<div class="alert alert-warning fonte-10">Estrutura não carregada para este produto.</div>';
        }

        $('#modal_estrutura_produto_titulo').text(
            codigo ? ' - ' + codigo + (anoMes ? ' - ' + anoMes : '') : ''
        );

        $('#conteudo_estrutura_produto_modal').html(html);

        $('#ModalEstruturaProduto').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#ModalEstruturaProduto').modal('show');
    }

/*************modal memoria de calculo*********************/
    function configurarModalMemoriaCalculo() {
        $(document).off('click.formacaoMemoriaCalculo', '#btn_ver_memoria_calculo');

        $(document).on('click.formacaoMemoriaCalculo', '#btn_ver_memoria_calculo', function () {
            abrirModalMemoriaCalculo();
        });
    }

    function abrirModalMemoriaCalculo() {
        if (typeof atualizarPrecoVenda === 'function') {
            atualizarPrecoVenda();
        }

        preencherModalMemoriaCalculo();

        $('#modalMemoriaCalculo').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#modalMemoriaCalculo').modal('show');
    }

    function preencherModalMemoriaCalculo() {
        var custoMateriais = parseBRNumber($('#custo_materiais').val());
        var custoMoi = parseBRNumber($('#custo_moi').val());
        var custoMoe = parseBRNumber($('#custo_moe').val());
        var freteValor = parseBRNumber($('#vl_frete').val());

        var custoProcessos = custoMoi + custoMoe;
        var custoTotal = custoMateriais + custoProcessos + freteValor;

        var precoVenda = parseBRNumber($('#preco_venda_calculado').val());

        var icms = parseBRNumber($('#parametro_icms').val());
        var simples = parseBRNumber($('#parametro_simples').val());
        var pis = parseBRNumber($('#parametro_pis').val());
        var cofins = parseBRNumber($('#parametro_cofins').val());

        var comissao = parseBRNumber($('#comissao').val());
        var fretePercentual = parseBRNumber($('#parametro_frete_percentual').val());
        var despesaFixa = parseBRNumber($('#parametro_despesa_fixa').val());
        var difal = parseBRNumber($('#parametro_difal').val());
        var irCsll = parseBRNumber($('#parametro_ir_csll').val());
        var outros = parseBRNumber($('#parametro_outros').val());

        var prazo = parseBRNumber($('#prazo').val());
        var taxaFinanceira = parseBRNumber($('#parametro_taxa_financeira').val());

        var margemInformada = parseBRNumber($('#margem').val());

        var percentualImpostos = icms + simples + pis + cofins;
        var percentualOutrasVariaveis = difal + fretePercentual + outros + comissao;

        var taxaFinanceiraEfetiva = (Math.pow((taxaFinanceira / 100) + 1, prazo / 30) - 1) * 100;

        if (isNaN(taxaFinanceiraEfetiva) || taxaFinanceiraEfetiva < 0) {
            taxaFinanceiraEfetiva = 0;
        }

        var equivalencia = Math.pow((taxaFinanceira / 100) + 1, prazo / 30);

        if (isNaN(equivalencia) || equivalencia <= 0) {
            equivalencia = 1;
        }

        var valorImpostos = precoVenda > 0
            ? precoVenda * (percentualImpostos / 100)
            : 0;

        var valorOutrasVariaveis = precoVenda > 0
            ? precoVenda * (percentualOutrasVariaveis / 100)
            : 0;

        var baseCustoFinanceiro = custoTotal + valorImpostos + valorOutrasVariaveis;

        var valorCustoFinanceiro = precoVenda > 0
            ? baseCustoFinanceiro * (taxaFinanceiraEfetiva / 100)
            : 0;

        var margemContribuicao =
            precoVenda
            - custoTotal
            - valorImpostos
            - valorOutrasVariaveis
            - valorCustoFinanceiro;

        var percentualMargemContribuicao = precoVenda > 0
            ? (margemContribuicao / precoVenda) * 100
            : 0;

        var valorDespesaFixa = precoVenda > 0
            ? precoVenda * (despesaFixa / 100)
            : 0;

        var valorFinanceiroDespesaFixa = valorDespesaFixa * (taxaFinanceiraEfetiva / 100);

        var resultadoBruto =
            margemContribuicao
            - valorDespesaFixa
            - valorFinanceiroDespesaFixa;

        var percentualResultadoBruto = precoVenda > 0
            ? (resultadoBruto / precoVenda) * 100
            : 0;

        var valorIrCsll = resultadoBruto > 0
            ? resultadoBruto * (irCsll / 100)
            : 0;

        var valorFinanceiroIrCsll = valorIrCsll * (taxaFinanceiraEfetiva / 100);

        var resultadoLiquido =
            resultadoBruto
            - valorIrCsll
            - valorFinanceiroIrCsll;

        var percentualResultadoLiquido = precoVenda > 0
            ? (resultadoLiquido / precoVenda) * 100
            : 0;

        var divisor = precoVenda > 0
            ? custoTotal / precoVenda
            : 0;

        var percentualVenda = divisor > 0
            ? (1 - divisor) * 100
            : 0;

        var formaCalculo = $('#forma_calculo').val() === '1'
            ? 'Preço informado'
            : 'Margem';

        $('#modal_memoria_calculo_titulo').text(montarTituloProdutoMemoria());

        $('#memoria_custo_materiais').text('R$ ' + formatCurrencyNumber(custoMateriais));
        $('#memoria_custo_processos').text('R$ ' + formatCurrencyNumber(custoProcessos));
        $('#memoria_custo_total').text('R$ ' + formatCurrencyNumber(custoTotal));
        $('#memoria_preco_venda').text('R$ ' + formatCurrencyNumber(precoVenda));

        $('#memoria_forma_calculo').text(formaCalculo);
        $('#memoria_percentual_venda').text(formatPercentNumber(percentualVenda) + '%');
        $('#memoria_divisor_venda').text(formatarDecimalMemoria(divisor, 6));

        $('#memoria_calculo').text(
            formatCurrencyNumber(custoTotal) + ' / ' + formatarDecimalMemoria(divisor, 6)
        );

        if ($('#forma_calculo').val() === '1') {
            $('#memoria_formula').text('memória calculada sobre o preço informado');
        } else {
            $('#memoria_formula').text('preco_venda = custo_total / divisor');
        }

        preencherPercentualMemoria('memoria_icms', 'memoria_icms_bar', icms);
        preencherPercentualMemoria('memoria_simples', 'memoria_simples_bar', simples);
        preencherPercentualMemoria('memoria_pis', 'memoria_pis_bar', pis);
        preencherPercentualMemoria('memoria_cofins', 'memoria_cofins_bar', cofins);

        $('#memoria_imposto_venda').text(formatPercentNumber(percentualImpostos) + '%');
        $('#memoria_valor_impostos').text('R$ ' + formatCurrencyNumber(valorImpostos));

        preencherPercentualMemoria('memoria_comissao', 'memoria_comissao_bar', comissao);
        preencherPercentualMemoria('memoria_frete_percentual', 'memoria_frete_percentual_bar', fretePercentual);
        preencherPercentualMemoria('memoria_despesa_fixa', 'memoria_despesa_fixa_bar', despesaFixa);

        $('#memoria_difal').text(formatPercentNumber(difal) + '%');
        $('#memoria_ir_csll').text(formatPercentNumber(irCsll) + '%');

        $('#memoria_valor_outras_variaveis').text('R$ ' + formatCurrencyNumber(valorOutrasVariaveis));

        $('#memoria_outros').text(formatPercentNumber(outros) + '%');

        if ($('#memoria_margem_lucro').length) {
            $('#memoria_margem_lucro').text(formatPercentNumber(margemInformada) + '%');
        }

        if ($('#memoria_margem_lucro_bar').length) {
            atualizarBarraPercentual('#memoria_margem_lucro_bar', margemInformada);
        }

        $('#memoria_prazo_medio').text(parseInt(prazo) + ' dias');
        $('#memoria_taxa_financeira').text(formatPercentNumber(taxaFinanceira) + '%');
        $('#memoria_equivalencia').text(formatarDecimalMemoria(equivalencia, 6));
        $('#memoria_markup').text(formatarDecimalMemoria(divisor, 6));

        $('#memoria_valor_custo_financeiro').text('R$ ' + formatCurrencyNumber(valorCustoFinanceiro));
        $('#memoria_valor_financeiro_despesa_fixa').text('R$ ' + formatCurrencyNumber(valorFinanceiroDespesaFixa));
        $('#memoria_valor_financeiro_ir_csll').text('R$ ' + formatCurrencyNumber(valorFinanceiroIrCsll));

        $('#memoria_valor_mc').text('R$ ' + formatCurrencyNumber(margemContribuicao));
        $('#memoria_percentual_mc').text(formatPercentNumber(percentualMargemContribuicao) + '%');

        $('#memoria_valor_rb').text('R$ ' + formatCurrencyNumber(resultadoBruto));
        $('#memoria_percentual_rb').text(formatPercentNumber(percentualResultadoBruto) + '%');

        $('#memoria_valor_liquido').text('R$ ' + formatCurrencyNumber(resultadoLiquido));
        $('#memoria_percentual_liquido').text(formatPercentNumber(percentualResultadoLiquido) + '%');

        atualizarCamposOcultosMemoria(
            valorImpostos,
            valorOutrasVariaveis,
            valorCustoFinanceiro,
            margemContribuicao,
            percentualMargemContribuicao,
            valorDespesaFixa,
            valorFinanceiroDespesaFixa,
            resultadoBruto,
            percentualResultadoBruto,
            valorIrCsll,
            valorFinanceiroIrCsll,
            resultadoLiquido,
            percentualResultadoLiquido
        );
    }

    function atualizarCamposOcultosMemoria(
        valorImpostos,
        valorOutrasVariaveis,
        valorCustoFinanceiro,
        margemContribuicao,
        percentualMargemContribuicao,
        valorDespesaFixa,
        valorFinanceiroDespesaFixa,
        resultadoBruto,
        percentualResultadoBruto,
        valorIrCsll,
        valorFinanceiroIrCsll,
        resultadoLiquido,
        percentualResultadoLiquido
    ) {
        $('#vl_impostos').text(formatCurrencyNumber(valorImpostos));
        $('#vl_outras_variaveis').text(formatCurrencyNumber(valorOutrasVariaveis));
        $('#vl_custo_financeiro_outras_variaveis').text(formatCurrencyNumber(valorCustoFinanceiro));

        $('#vlr_mc').text(formatCurrencyNumber(margemContribuicao));
        $('#prec_mc').text(formatPercentNumber(percentualMargemContribuicao));

        $('#vl_despesas_fixas').text(formatCurrencyNumber(valorDespesaFixa));
        $('#vl_financeiro_despesas_fixas').text(formatCurrencyNumber(valorFinanceiroDespesaFixa));

        $('#vlr_rb').text(formatCurrencyNumber(resultadoBruto));
        $('#prec_rb').text(formatPercentNumber(percentualResultadoBruto));

        $('#vl_ir_csl').text(formatCurrencyNumber(valorIrCsll));
        $('#vl_financeiro_ir_csl').text(formatCurrencyNumber(valorFinanceiroIrCsll));

        $('#vlr_liquido').text(formatCurrencyNumber(resultadoLiquido));
        $('#prec_liquido').text(formatPercentNumber(percentualResultadoLiquido));
    }

    function montarTituloProdutoMemoria() {
        var codigo = $('#codigo_resultado').val() || $('#codigo_produto').val() || '';
        var anoMes = $('#ano_mes_custo').val() || '';

        if (!codigo) {
            return '';
        }

        if (anoMes) {
            return ' - ' + codigo + ' - ' + anoMes;
        }

        return ' - ' + codigo;
    }

    function preencherPercentualMemoria(idTexto, idBarra, valor) {
        var percentual = parseBRNumber(valor);

        $('#' + idTexto).text(formatPercentNumber(percentual) + '%');
        atualizarBarraPercentual('#' + idBarra, percentual);
    }

    function atualizarBarraPercentual(seletor, percentual) {
        percentual = parseBRNumber(percentual);

        if (percentual < 0) {
            percentual = 0;
        }

        if (percentual > 100) {
            percentual = 100;
        }

        $(seletor).css('width', percentual + '%');
    }

    function formatarDecimalMemoria(valor, casas) {
        casas = casas === undefined ? 6 : casas;

        valor = parseFloat(valor) || 0;

        return valor.toFixed(casas).replace('.', ',');
    }

    function textoOuZero(valor) {
        valor = $.trim(valor || '');

        if (valor === '') {
            return '0,00';
        }

        return valor;
    }

    function garantirPercentual(valor) {
        valor = $.trim(valor || '0,00');

        if (valor.indexOf('%') === -1) {
            return valor + '%';
        }

        return valor;
    }
