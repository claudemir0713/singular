// Garante que dentro deste arquivo o $ sempre é o jQuery
jQuery(function ($) {

    $(document).find('select').chosen();
    // verificaTipoData();

    /**********sempre que tabalhar com Ajax no Laravel tem que incluir essa tag *************/
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    /***********************colocando duas casas decimais************************************* */
    var decimal = $('.floatNumberField').attr('decimal');
    $('.floatNumberField').val(parseFloat($('.floatNumberField').val()).toFixed(decimal));

    $(".floatNumberField").on('change', function () {
        var decimal = $(this).attr('decimal');
        $(this).val(parseFloat($(this).val()).toFixed(decimal));
    });
    /**********************formata numero **************************************************/
    const formCurrency = new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2
    })


    /**********************formata cub ****************************************************/
    const formCub = new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 4
    })

    /*********************converte valor texto para valor************************************/
    function formataValor(valor) {
        let vlr_txt = valor.replaceAll('.', '').replaceAll(',', '.');
        let vlr_float = parseFloat(vlr_txt)
        return vlr_float
    }
    /*************************pegando a url do servidor**************************************/

    url = $('input#appurl').val();

    /************************ buscaCep ******************************************************/
    $(document).on('blur', 'input#Cep', function (event) {
        event.preventDefault() // não permite que o navegador faça o submit
        var cep = $(this).val();
        var endereco = $('input#Ender').val().trim();
        if (endereco == '') {
            buscaCep(cep);
        };
    })

    /************************ buscaCnpj ******************************************************/
    $(document).on('blur', 'input#cnpj', function (event) {
        var cnpj = $(this).val().replace('.', '').replace('/', '').replace('-', '');

        if (cnpj.length >= 14) {
            buscaCnpj(cnpj);
        };
    })


    /***********************mensagem confirma exclusão **************************************/
    $(document).on('click', '.delete', function (event) {
        event.preventDefault()
        Swal({
            title: 'Deseja realmente excluir?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Remover'
        }).then((result) => {
            if (result.value) {
                var form = $(this).parent()
                form.submit()
            }
        });
    })


    /**********************time intervel *********************************************************************/
    // atualizaCards();
    // setInterval(function(){
    //     atualizaCards();
    // }, 5000);


    /**********************gravar menu com ajax **************************************************/
    $(document).on('submit', 'form#cadastro-menu', function (event) {
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = 'menu'

        var descricao = $(this).find('input#descricao').val();
        var tipo = $(this).find('select#tipo').val();
        var ordem = $(this).find('input#ordem').val();
        var rota = $(this).find('input#rota').val();
        var icone = $(this).find('input#icone').val();


        /********************************************************************************************* */
        if (!descricao || !tipo || !ordem) {
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer: 3000
            })
        } else {
            var dados = {
                'descricao': descricao
                , 'tipo': tipo
                , 'ordem': ordem
                , 'rota': rota
                , 'icone': icone
            }
            cadastrar(dados, route, type, origem);
        }
    })
    /***********************liberaMenu *****************************/
    $('#usuario').on('change', function () {
        liberaMenuDisponivel();
        removeMenuLiberado();
    })

    $(document).on('click', 'input.disponivel', function (event) {
        if ($(this).is(":checked")) {
            var disponivelId = $(this).val();
            var usuario = $(document).find('#usuario').val();
            addMenuUsuario(disponivelId, usuario)
        } else {
            var liberadoId = $(this).val();
            removeMenuUsuario(liberadoId)
        }
    })
    $(document).on('click', 'button.liberado', function (event) {
        var liberadoId = $(this).val();
        removeMenuUsuario(liberadoId)
    })


    /**********************AtivaInativaUsuario**************************************************/
    $(document).on('click', 'input.cliente_ativo', function (event) {
        var usuario_id = $(this).val();
        var route = '/usuario/ativaUsuario'
        if ($(this).is(":checked")) {
            var ativo = 'S';
        } else {
            var ativo = 'N';
        }
        ativaUsuario(usuario_id, ativo, route)
    })
    /**********************AtivaInativaUsuario**************************************************/
    $(document).on('click', 'input.cliente_nivel', function (event) {
        var usuario_id = $(this).val();
        var route = '/usuario/nivelUsuario'
        if ($(this).is(":checked")) {
            var nivel = 'adm';
        } else {
            var nivel = 'usuário';
        }
        nivelUsuario(usuario_id, nivel, route)
    })


    /**********************gravar base com ajax **************************************************/
    $(document).on('submit', 'form#cadastro-base', function (event) {
        event.preventDefault()
        var route = $(this).find('input#route').val();
        var type = $(this).find('input#type').val();
        var origem = $(this).find('input#origem').val();

        var name = $(this).find('input#name').val();
        var base = $(this).find('input#base').val();
        var office = $(this).find('input#office').val();
        var codEmp = $(this).find('input#codEmp').val();
        var dados = {
            'name': name
            , 'base': base
            , 'office': office
            , 'codEmp': codEmp
        }

        if (!name || !base || !office) {
            Swal({
                title: 'Preencha todos os campos obrigatório',
                type: 'error',
                timer: 3000
            })
        } else {
            cadastrar(dados, route, type, origem);
        }
    })

    /**************************busca codigo questor**************************************************/
    $(document).on('change', '#financeiro', function () {
        let codFinacneiro = $(this).val();
        codQuestor(codFinacneiro)
    })

    /*************************marcar todos os checkbox******************************/
    $(document).on('change', '.checked', function () {
        let checked = $(this).is(":checked");
        $(document).find('.selecionado').prop("checked", checked)
    })

    /***************************atualiza Base Comissão******************************/
    $(document).on('dblclick', '.baseComissao', function (event) {
        event.preventDefault();
        let con_codigo = $(this).attr('con_codigo');
        let con_bc_comissao = $(this).val();
        let origem = 'dbclick';
        baseComissao(con_codigo, con_bc_comissao, origem)
    })
    $(document).on('keydown', '.baseComissao', function (e) {
        e = e || window.event;
        let code = e.which || e.keyCode;
        if (code == 13) {
            e.preventDefault();
            let con_codigo = $(this).attr('con_codigo');
            let con_bc_comissao = $(this).val();
            let origem = 'dbclick';
            baseComissao(con_codigo, con_bc_comissao, origem)
        }
    })
    $(document).on('change', '.baseComissao', function (event) {
        event.preventDefault();
        let con_codigo = $(this).attr('con_codigo');
        let con_bc_comissao = $(this).val();
        let origem = 'blur';
        baseComissao(con_codigo, con_bc_comissao, origem)
    })
    // /**************************imprimir contas a receber************************************/
    // $(document).on('click', '#btn-imprimirContasReceber', function () {
    //     let dataI = ($(document).find('#dtI').val()) ? $(document).find('#dtI').val() : ''
    //     let dataF = ($(document).find('#dtF').val()) ? $(document).find('#dtF').val() : ''
    //     let cliente = ($(document).find('#cliente').val()) ? $(document).find('#cliente').val() : ''
    //     let vendedor = ($(document).find('#vendedor').val()) ? $(document).find('#vendedor').val() : ''
    //     let tipo = ($(document).find('#tipo').val()) ? $(document).find('#tipo').val() : ''
    //     let status = ''
    //     let nf = ($(document).find('#nf').val()) ? $(document).find('#nf').val() : ''
    //     let dtIEmissa = ($(document).find('#dtIEmissa').val()) ? $(document).find('#dtIEmissa').val() : ''
    //     let dtFEmissa = ($(document).find('#dtFEmissa').val()) ? $(document).find('#dtFEmissa').val() : ''
    //     let route = url + '/receber/imprimir?dtI=' + dataI + '&dtF=' + dataF + '&cliente=' + cliente + '&vendedor=' + vendedor + '&tipo=' + tipo + '&status=' + status + '&nf=' + nf + '&dtIEmissa=' + dtIEmissa + '&dtFEmissa=' + dtFEmissa;
    //     window.open(route, '_blank');
    // })

    /************************** imprimir contas a receber ************************************/
    $(document).on('click', '#btn-imprimirContasReceber', function () {
        let dataI = $(document).find('#dtI').val() ? $(document).find('#dtI').val() : '';
        let dataF = $(document).find('#dtF').val() ? $(document).find('#dtF').val() : '';
        let cliente = $(document).find('#cliente').val() ? $(document).find('#cliente').val() : '';
        let vendedor = $(document).find('#vendedor').val() ? $(document).find('#vendedor').val() : '';
        let tipo = $(document).find('#tipo').val() ? $(document).find('#tipo').val() : '';
        let status = '';
        let nf = $(document).find('#nf').val() ? $(document).find('#nf').val() : '';
        let dtIEmissa = $(document).find('#dtIEmissa').val() ? $(document).find('#dtIEmissa').val() : '';
        let dtFEmissa = $(document).find('#dtFEmissa').val() ? $(document).find('#dtFEmissa').val() : '';

        let tipoPgto = $(document).find('#tipo_pgto').val();

        if (!Array.isArray(tipoPgto)) {
            tipoPgto = tipoPgto ? [tipoPgto] : [];
        }

        tipoPgto = tipoPgto.filter(function (item, index, array) {
            return item && array.indexOf(item) === index;
        });

        let parametros = {
            dtI: dataI,
            dtF: dataF,
            cliente: cliente,
            vendedor: vendedor,
            tipo: tipo,
            status: status,
            nf: nf,
            dtIEmissa: dtIEmissa,
            dtFEmissa: dtFEmissa,
            tipo_pgto: tipoPgto
        };

        let route = url + '/receber/imprimir?' + $.param(parametros);

        window.open(route, '_blank');
    });
    /**************************imprimir contas a receber************************************/
    $(document).on('click', '#btn-imprimirComissaoPagar', function () {
        let dataI = ($(document).find('#dtI').val()) ? $(document).find('#dtI').val() : ''
        let dataF = ($(document).find('#dtF').val()) ? $(document).find('#dtF').val() : ''
        let cliente = ($(document).find('#cliente').val()) ? $(document).find('#cliente').val() : ''
        let vendedor = ($(document).find('#vendedor').val()) ? $(document).find('#vendedor').val() : ''
        let tipo = ($(document).find('#tipo').val()) ? $(document).find('#tipo').val() : ''
        let status = ''
        let nf = ($(document).find('#nf').val()) ? $(document).find('#nf').val() : ''
        let route = url + '/comissao/imprimirComissaoPagar?dtI=' + dataI + '&dtF=' + dataF + '&cliente=' + cliente + '&vendedor=' + vendedor + '&nf=' + nf;
        window.open(route, '_blank');
    })

    /***************************btnExportaContabil******************************************/
    $(document).on('click', '.btnExportaContabil', function () {
        btnExportaContabil();
    })

    /***************************btnGeraSped******************************************/
    $(document).on('click', '.btnGeraSped', function () {
        btnGeraSped();
    })

    /***************************btnFechaEstoque******************************************/
    $(document).on('click', '.btnFechaEstoque', function () {
        btnFechaEstoque();
    })

    /***************************btnAtualizaEstoque******************************************/
    $(document).on('click', '.btnAtualizaEstoque', function () {
        btnAtualizaEstoque();
    })

    /***************************btnAtualizaHorasMLC******************************************/
    $(document).on('click', '.btnAtualizaHorasMLC', function () {
        btnAtualizaHorasMLC();
    })

    /***************************busca produto com ficha**************************************/

    $(document).on('change', '#pro_descr', function () {
        let pro_descr = $(this).val();
        buscaProdutoComFicha(pro_descr);
    })

    /***************************papeleta*****************************************************/
    $(document).on('blur', '#carga', function () {
        const carga = $(this).val().trim();
        const novaUrl = `${url}/vendas/cargas_pdf/${carga}`;
        $("#btnPdf").attr("href", novaUrl);

        if (carga) dadosPapeleta(carga);
    })
    $(document).on('keypress', '#carga', function (e) {
        if (e.which === 13) { // Enter
            e.preventDefault();
            const carga = $(this).val().trim();
            const novaUrl = `${url}/vendas/cargas_pdf/${carga}`;
            $("#btnPdf").attr("href", novaUrl);

            if (carga) dadosPapeleta(carga);
        }
    })
    $(document).on('blur keyup', '#vendedor', function () {
        const carga = $("#carga").val().trim();

        if (carga) {
            dadosPapeleta(carga);
        }
    });

    /************************ atualiza-papeleta ************************************************/
    $(document).on('change', '.atualiza-papeleta', function (e) {
        atualiza_papeleta()
    });
    /************************ atualiza-papeleta-dav ************************************************/
    $(document).on('change', '.atualiza-papeleta-frete', function (e) {
        let id_dav = $(this).attr('id_dav')
        atualiza_papeleta_frete(id_dav)
    });
    /************************ atualiza-papeleta-parcela ************************************************/
    $(document).on('change', '.atualiza-papeleta-parcela', function (e) {
        let parcela_id = $(this).attr('parcela_id')
        let check = $(this).is(':checked') ? 'S' : 'N';
        atualiza_papeleta_parcela(parcela_id, check)
    });

});
