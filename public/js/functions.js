/**********************formata numero **************************************************/
const formCurrency = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
    minimumFractionDigits: 2
})

/*********************converte valor texto para valor************************************/
function formataValor(valor){
    let vlr_txt = valor.replaceAll('.','').replaceAll(',','.');
    let vlr_float = parseFloat(vlr_txt)
    return vlr_float
}

/********************formata data************************************/
function formataDataBR(dataISO) {
    if (!dataISO) return "";
    const partes = dataISO.split("-"); // [yyyy, mm, dd]
    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}



// --- Helpers ---------------------------------------------------
    function formataDataBR(dataISO) {
        if (!dataISO) return "";
        const s = String(dataISO).slice(0, 10); // "YYYY-MM-DD"
        const [yyyy, mm, dd] = s.split("-");
        if (!yyyy || !mm || !dd) return dataISO;
        return `${dd}/${mm}/${yyyy}`;
    }

    function formatCurrencyBR(v) {
        const n = Number(v ?? 0);
        return n.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function safeText(v) {
        return v == null ? '' : String(v);
    }



/********************* busca cep cliente *****************************************/
function buscaCep(cep){
    $.ajax({
        data: {cep:cep},
        type: 'POST',
        dataType: 'JSON',
        url:url+'/clientes/buscaCep',
        beforeSend: function(){

        },
        success: function(result)
        {
            $('#Mun').val(result.localidade);
            $('#Ender').val(result.logradouro);
            $('#Bairro').val(result.bairro);
            $('#Uf').val(result.uf);
        }
    });

}

/*****************************busca cnpj*****************************************/
function buscaCnpj(cnpj){
    $.ajax({
        data: {cnpj:cnpj},
        type: 'POST',
        dataType: 'JSON',
        url:url+'/clientes/buscaCnpj',
        beforeSend: function(){
            Swal({
                title: 'Aguarde consultado dados!',
                type: 'warning',
                timer:2000
            })
        },
        success: function(result)
        {
            $('input#nome').val(result.nome);
            $('input#cep').val(result.cep);
            $('input#telefone').val(result.telefone);
            $('input#cidade').val(result.municipio);
            $('input#email').val(result.email);
            $('input#endereco').val(result.logradouro+','+result.numero);
            $('input#bairro').val(result.bairro);
            $('input#uf').val(result.uf);

        }
    });
}


function atualizaCards(){
    $.ajax({
        data: '',
        type: 'post',
        url:url+'/home/atualizaCard',
        dataType: 'JSON',
        error: function(result){
        },
        success: function(result)
        {
            $.each(result, function(i, val){
                $('#span-nr'+val.etapa.replace(/\s/g, '')).html(val.qtd)
            });
        }

    })
}

/*******************************calcula PMT **************************************/
function PMT(ir,np, pv, fv = 0){
    var  fator = 0;
    fator = Math.pow((1 + ir), np);
    var pmt = ir * pv  * (fator + fv)/(fator-1);
    return pmt;
}

/***********************************cadastrar************************************ */
function cadastrar(dados,route,type,origem){
    var title = 'Cadastro alterado com sucesso!';
    if(type == 'POST'){
        title = 'Cadastro efetuado com sucesso!';
    }
    var tipo = 'success';
    let tempo = 500;
    $.ajax({
        data: dados,
        type: type,
        dataType: 'JSON',
        url:url+route,
        success: function(result)
        {
            if(result=="Existe"){
                title="Já existe esse cadastro";
                tipo = 'info';
                tempo = 2000;
            }else if(result!="success"){
                title="Cadastro não efetuado";
                tipo = 'error';
                tempo = 2000;
                console.log(result)
            }else{
                if(type!='POST'){
                    window.location.replace(url+'/'+origem);
                }else if(origem != 'usuarioBaseNovo'){
                    window.location.reload();
                    $('.limpar').val('');
                    $('select').trigger("chosen:updated");
                }
            }
            Swal({
                title: title,
                type: tipo,
                timer:tempo
            })
    },
        complete: function(){
            // $('#salvar').prop("disabled",false);
        }
    })
}

function liberaMenuDisponivel()
{
    var usuario = $(document).find('#usuario').val();
    var dados = {
        'usuario': usuario
    };
    var route = '/menu/disponivel'
    var linhas = '';
    $.ajax({
        data: dados,
        type: 'post',
        dataType: 'JSON',
        url: url + route,
        beforeSend : function(){
            linhas = '';
            $('#menuDisponivel').html('');
            swal({
                title: 'Aguarde!',
                type: 'warning',
                html: '<strong>Efetuando busca</strong>',
                onOpen: () => {
                    swal.showLoading()
                }
            })
        },
        success: function (result) {
            linhas = '';
            classe = '';
            $.each(result, function (i, val) {
                if(val.tipo=='Título'){
                    classe='negrito';
                }else{
                    classe='paragrafo';
                };
                var id = 0;
                (val.selecionado=="checked")?id = val.selecionadoId : id=val.disponivelId
                linhas += '<tr>';
                    linhas += '<td class="'+classe+'"><button class="btn btn-link" value="'+val.disponivelId+'">'+val.ordem+'-'+val.descricao+'</button></td>';
                    linhas += '<td>';
                        linhas += '<label class="switch" >';
                            linhas += '<input type="checkbox" class="disponivel" id="protrang" name="protrang" '+val.selecionado+' value="'+id+'">';
                            linhas += '<span class="slider round"></span>';
                        linhas += '</label>';
                    linhas += '</td>';
                linhas += '</tr>';
            })

        },
        complete:function(){
            $('#menuDisponivel').html(linhas);
            swal.close();
        }
    })
}

function removeMenuLiberado()
{
    var usuario = $(document).find('#usuario').val();
    var dados = {
        'usuario': usuario
    };
    var route = '/menu/menuLiberado'
    var linhas = '';
    $.ajax({
        data: dados,
        type: 'post',
        dataType: 'JSON',
        url: url + route,
        beforeSend : function(){
            linhas = '';
            $('#menuLiberado').html('');
            swal({
                title: 'Aguarde!',
                type: 'warning',
                html: '<strong>Efetuando busca</strong>',
                onOpen: () => {
                    swal.showLoading()
                }
            })
        },
        success: function (result) {
        },
        complete:function(){
            $('#menuLiberado').html(linhas);
            swal.close();
        }
    })
}

function addMenuUsuario(disponivelId,usuario){
    var dados = {
        'usuario': usuario,
        'disponivelId' : disponivelId
    };
    var route = '/menu/addMenuUsuario'
    $.ajax({
        data: dados,
        type: 'post',
        dataType: 'JSON',
        url: url + route,
        complete:function(){
            liberaMenuDisponivel();
            removeMenuLiberado();
        }
    })
}
function removeMenuUsuario(liberadoId){
    var dados = {
        'liberadoId' : liberadoId
    };
    var route = '/menu/removeMenuUsuario'
    $.ajax({
        data: dados,
        type: 'post',
        dataType: 'JSON',
        url: url + route,
        complete:function(){
            liberaMenuDisponivel();
            removeMenuLiberado();
        }
    })
}

function ativaUsuario(usuario_id,ativo,route){
    var dados = {
        'usuario_id': usuario_id,
        'ativo' : ativo
    };
    $.ajax({
        data: dados,
        type: 'post',
        dataType: 'JSON',
        url: url + route
    })
}

function baseComissao(con_codigo,con_bc_comissao,origem){
    let bgcolor = '';
    let valor_original = formataValor($(document).find('.valor_original'+con_codigo).html())
    let baseConvertida = formataValor(con_bc_comissao);
    let ipi_frete = formataValor($(document).find('.ipi_frete'+con_codigo).html())
    let perc_comissao = formataValor($(document).find('.perc_comissao'+con_codigo).html())


    if(origem=='dbclick'){
        baseConvertida = valor_original - ipi_frete;
    }

    if((valor_original.toFixed(2)) != ((baseConvertida+ipi_frete).toFixed(2))){bgcolor = 'red'};

    let comissao = baseConvertida * (perc_comissao/100);
    comissao = formCurrency.format(comissao).replace('R$ ','');
    let baseRetorno = formCurrency.format(baseConvertida).replace('R$ ','');

    $(document).find('.comissao'+con_codigo).html(comissao);
    $(document).find('#'+con_codigo).val(baseRetorno);

    let route = '/comissao/alteraBase';
    let dados = {
        'con_codigo'        : con_codigo,
        'con_bc_comissao'   : baseConvertida
    };
    $.ajax({
        data: dados,
        type: 'post',
        dataType: 'JSON',
        url: url + route,
        beforeSend: function(){
            Swal({
                title: 'Aguarde!',
                type: 'warning',
                timer:2000
            })
        },
        success:function(result){
            $(document).find('.linha'+con_codigo).attr('bgcolor',bgcolor);
        },
        complete:function(){
            Swal.close();
        }
    })

}

function btnExportaContabil(){
    let dtI = $(document).find('#dtI').val();
    let dtF = $(document).find('#dtF').val();
    let tipo = $(document).find('#tipo').val();
    let arquivo = '';

    if(tipo==0){
        arquivo = 'Contas a Receber'+dtI+'-'+dtF+'.txt';
    }else{
        arquivo = 'Contas a Pagar'+dtI+'-'+dtF+'.txt';
    }

    let route = '/contabilidade/geraArquivoBiaxas';
    let dados = {
        'dtI'        : dtI,
        'dtF'        : dtF,
        'tipo'       : tipo,
    };

    $.ajax({
        data: dados,
        type: 'post',
        dataType: 'JSON',
        url: url + route,
        beforeSend: function(){
            $(document).find('.linkExportaContabil').html('')
            Swal({
                title: 'Aguarde!',
                type: 'warning',
                // timer:2000
            })
        },
        success:function(result){
            let link = '<h4><a href="download/'+arquivo+'" target="_blank"><span class="fas fa-download"></span> '+arquivo+'</a></h4>';
            console.log(link);
            if(result==1){
                Swal({
                    title: 'Arquivo gerado com sucesso!',
                    type: 'success',
                    timer:2000
                })
                $(document).find('.linkExportaContabil').html(link)
            }
        },
        complete:function(){
        }
    })

}

function btnGeraSped(){
    let dtI = $(document).find('#dtI').val();
    let dtF = $(document).find('#dtF').val();
    let cod_fin = $(document).find('#cod_fin').val();
    let arquivo = 'Sped'+dtI+'-'+dtF+'.txt';

    let route = '/contabilidade/geraSped';
    let dados = {
        'dtI'        : dtI,
        'dtF'        : dtF,
        'cod_fin'    : cod_fin,
    };

    $.ajax({
        data: dados,
        type: 'post',
        dataType: 'JSON',
        url: url + route,
        beforeSend: function(){
            Swal({
                title: 'Aguarde!',
                type: 'warning',
                // timer:2000
            })
        },
        success:function(result){
            let link = '<h4><a href="download/'+arquivo+'" target="_blank"><span class="fas fa-download"></span> '+arquivo+'</a></h4>';
            console.log(link);
            if(result==1){
                Swal({
                    title: 'Arquivo gerado com sucesso!',
                    type: 'success',
                    timer:2000
                })
                $(document).find('.linkExportaContabil').html(link)
            }
        },
        complete:function(){
        }
    })
}

function btnFechaEstoque(){
    let ano = $(document).find('#ano').val();
    let mes = $(document).find('#mes').val();

    let route = '/contabilidade/geraEstoque';
    let dados = {
        'ano'        : ano,
        'mes'        : mes,
    };

    $.ajax({
        data: dados,
        type: 'post',
        dataType: 'JSON',
        url: url + route,
        beforeSend: function(){
            Swal({
                title: 'Aguarde!',
                type: 'warning',
                // timer:2000
            })
        },
        success:function(result){
        },
        complete:function(){
            Swal({
                title: 'Arquivo processado!',
                type: 'success',
                timer:2000
            })
        }
    })
}

function btnAtualizaEstoque(){
    let texto = $(document).find('#texto').val();

    let route = '/contabilidade/geraAcertoEstoque';
    let dados = {
        'texto'        : texto,
    };

    $.ajax({
        data: dados,
        type: 'post',
        dataType: 'JSON',
        url: url + route,
        beforeSend: function(){
            Swal({
                title: 'Aguarde!',
                type: 'warning',
                // timer:2000
            })
        },
        success:function(result){
            console.log(result);
            $(document).find('#texto').val('');
            $(document).find('#texto').val(result)
        },
        complete:function(){
            Swal({
                title: 'Arquivo processado!',
                type: 'success',
                timer:2000
            })
        }
    })
}

function btnAtualizaHorasMLC(){
    let texto = $(document).find('#texto').val();

    let route = '/fechamento/gravaHorasMLC';
    let dados = {
        'texto'        : texto,
    };

    $.ajax({
        data: dados,
        type: 'post',
        dataType: 'JSON',
        url: url + route,
        beforeSend: function(){
            Swal({
                title: 'Aguarde!',
                type: 'warning',
                // timer:2000
            })
        },
        success:function(result){
            console.log(result);
            // $(document).find('#texto').val('');
            // $(document).find('#texto').val(result)
        },
        complete:function(){
            Swal({
                title: 'Arquivo processado!',
                type: 'success',
                timer:2000
            })
        }
    })
}


function buscaProdutoComFicha(pro_descr){
    let route = '/fechamento/consutaProdutoComFicha';
    let dados = {
        'pro_descr'        : pro_descr,
    };

    $.ajax({
        data: dados,
        type: 'post',
        dataType: 'JSON',
        url: url + route,
        beforeSend: function(){
            $(document).find("#produtosFicha tbody").empty();
        },
        success:function(result){
            let rowsHtml = '';
            $.each(result, function(i, val){
                rowsHtml +='<tr>'
                    rowsHtml +='<td>'+val.PRD_CODIGO+'</td>'
                    rowsHtml +='<td>'+val.PRD_DESCRICAO+'</td>'
                    rowsHtml +='<td>'+val.QT_COMP+'</td>'
                rowsHtml +='</tr>'
            });
            $(document).find('#produtosFicha tbody').append(rowsHtml);
        }
    })

}




//**************************papeleta****************************************************
// -----------------------------------------------
// Util: escapar HTML (mantida exatamente como você enviou)
// -----------------------------------------------
function esc(v) {
    if (v === null || v === undefined) return "";
    return String(v)
        .replace(/&amp;amp;/g, "&amp;amp;amp;")
        .replace(/&amp;lt;/g, "&amp;amp;lt;")
        .replace(/&amp;gt;/g, "&amp;amp;gt;")
        .replace(/"/g, "&amp;amp;quot;")
        .replace(/'/g, "&amp;amp;#039;");
}

// -----------------------------------------------
// Tabela de parcelas (com checkbox no início)
// - Checkbox recebe data-parcela-id = dav_parcela_id (fallback para id)
// - Fica marcado quando considera === "S"
// -----------------------------------------------
function criarTabelaParcelas(parcelas) {
    if (!Array.isArray(parcelas) || parcelas.length === 0) {
        return `
            <div class="text-muted" style="padding: .25rem 0 .5rem 0;">
                Sem parcelas para exibir.
            </div>
        `;
    }

    const linhas = parcelas.map((p, idx) => {
        const parcelaId = p.dav_parcela_id ?? p.id ?? "";
        const parcela   = p.parcela ?? "—";
        const tipo      = p.tipo ?? "—";
        const considera = p.considera ?? "—";
        const venc      = p.vencimento ?? "—";
        const valor     = p.valor != null
            ? Number(p.valor).toLocaleString("pt-BR", { style: "currency", currency: "BRL" })
            : "—";
        const cobranca  = p.cobranca ?? "—";

        const checkedAttr = (String(considera).toUpperCase() === "S") ? "checked" : "";
        const inputId = `chk-parcela-${parcelaId || idx}`;

        return `
            <tr>
                <td class="text-center" style="width:36px;">
                    <input
                        type="checkbox"
                        class="form-check-input atualiza-papeleta-parcela"
                        id="${esc(inputId)}"
                        parcela_id="${esc(parcelaId)}"
                        ${checkedAttr}
                    >
                </td>
                <td>${esc(parcela)}</td>
                <td>${esc(tipo)}</td>
                <td>${formataDataBR(esc(venc))}</td>
                <td class="text-end">${esc(valor)}</td>
                <td>${esc(cobranca)}</td>
            </tr>
        `;
    }).join("");

    return `
        <div class="table-responsive">
            <table class="table table-sm mb-2">
                <thead class="table-light">
                    <tr>
                        <th style="width:36px;"></th>
                        <th>Parcela</th>
                        <th>Tipo</th>
                        <th>Vencimento</th>
                        <th class="text-end">Valor</th>
                        <th>Cobrança</th>
                    </tr>
                </thead>
                <tbody>${linhas}</tbody>
            </table>
        </div>
    `;
}

// -----------------------------------------------
// Agrupamento por DAV (parcela.dav -> dav.dav)
// -----------------------------------------------
function agruparParcelasPorDav(dadosDAV, dadosParcelas) {
    const grupos = new Map(); // key: índice do DAV, value: array de parcelas
    dadosDAV.forEach((_, i) => grupos.set(i, []));

    // Mapa de dav -> índice do DAV
    const mapDavToIndex = new Map();
    dadosDAV.forEach((dav, i) => {
        const chave = (dav && dav.dav != null) ? String(dav.dav) : null;
        if (chave) mapDavToIndex.set(chave, i);
    });

    const sobras = [];

    for (const p of (dadosParcelas || [])) {
        const chaveParcela = (p && p.dav != null) ? String(p.dav) : null;
        if (chaveParcela && mapDavToIndex.has(chaveParcela)) {
            const idx = mapDavToIndex.get(chaveParcela);
            grupos.get(idx).push(p);
        } else {
            sobras.push(p);
        }
    }

    return { grupos, sobras };
}

// -----------------------------------------------
// Render: Cards (um por linha) com parcelas separadas por "tipo"
// - Frete e Cobrança como <input class="form-control"> com id_dav="<dav>"
// - Largura Frete e Cobrança: col-md-2 col-2
// -----------------------------------------------
function renderCardsDAVComParcelasPorTipo(dadosDAV, dadosParcelas) {
    if (!Array.isArray(dadosDAV) || dadosDAV.length === 0) {
        return '<p class="text-muted mb-0">Nenhum DAV encontrado.</p>';
    }

    const { grupos, sobras } = agruparParcelasPorDav(dadosDAV, dadosParcelas);

    const cards = dadosDAV.map((dav, idx) => {
        const davNum      = dav.dav;
        const vendedor    = dav.vendedor
        const cliente     = dav?.cliente ?? "—";
        const freteExib   = dav?.frete_valor ?? "";  // input: vazio se inexistente
        const cobrExib    = dav?.cobranca ?? "";     // input: vazio se inexistente

        const parcelasDoDav = grupos.get(idx) || [];

        // ---- Agrupa por tipo (ex.: NF, PD, etc.) ----
        const porTipo = parcelasDoDav.reduce((acc, p) => {
            const t = p?.tipo ?? "—";
            if (!acc[t]) acc[t] = [];
            acc[t].push(p);
            return acc;
        }, {});

        // Ordem preferida: NF, PD, depois alfabética para outros
        const preferencia = ["NF", "PD"];
        const tipos = Object.keys(porTipo);
        const outros = tipos.filter(t => !preferencia.includes(t)).sort((a, b) => a.localeCompare(b));
        const ordemTipos = [...preferencia.filter(t => tipos.includes(t)), ...outros];

        const secoesTipos = ordemTipos.map(tipo => {
            const tabela = criarTabelaParcelas(porTipo[tipo]);
            return `
                <div class="mb-2">
                    <div class="small text-muted mb-1">Parcelas — ${esc(tipo)}</div>
                    ${tabela}
                </div>
            `;
        }).join("") || `
            <div class="text-muted" style="padding: .25rem 0 .5rem 0;">
                Sem parcelas vinculadas.
            </div>
        `;

        const freteInputId = `frete-dav-${esc(davNum)}`;
        const cobrInputId  = `cobranca-dav-${esc(davNum)}`;

        return `
            <div class="col-12 mb-3">
                <div class="card fonte-10 h-100">
                    <div class="card-header bg-info text-white">DAV #${esc(davNum)} ${esc(vendedor)}</div>
                    <div class="card-body">
                        <div class="form-row row">
                            <div class="form-group col-12">
                                <label class="mb-0">Cliente</label>
                                <div class="form-control-plaintext fw-semibold" style="padding-left:0;">
                                    ${esc(cliente)}
                                </div>
                            </div>

                            <div class="form-group col-md-2 col-2">
                                <label class="mb-0" for="${esc(freteInputId)}">Frete</label>
                                <input
                                    type="text"
                                    class="form-control money-br atualiza-papeleta-frete"
                                    id="${esc(freteInputId)}"
                                    id_dav="${esc(davNum)}"
                                    data-id-dav="${esc(davNum)}"
                                    name="frete"
                                    value="${esc(freteExib)}"
                                    placeholder="Informe o frete"
                                >
                            </div>

                            <div class="form-group col-md-2 col-2">
                                <label class="mb-0" for="${esc(cobrInputId)}">Cobrança</label>
                                <input
                                    type="text"
                                    class="form-control atualiza-papeleta-frete"
                                    id="${esc(cobrInputId)}"
                                    id_dav="${esc(davNum)}"
                                    data-id-dav="${esc(davNum)}"
                                    name="cobranca"
                                    value="${esc(cobrExib)}"
                                    placeholder="Informe a cobrança"
                                >
                            </div>
                        </div>

                        <hr class="my-2">

                        <!-- Tabelas por tipo -->
                        ${secoesTipos}
                    </div>
                </div>
            </div>
        `;
    }).join("");

    // Opcional: card com parcelas sem vínculo
    const cardSobras = (sobras && sobras.length > 0)
        ? `
            <div class="col-12 mb-3">
                <div class="card fonte-10">
                    <div class="card-header">Parcelas sem vínculo (campo dav não casou com o dav do card)</div>
                    <div class="card-body p-0">
                        ${criarTabelaParcelas(sobras)}
                    </div>
                </div>
            </div>
        `
        : "";

    return `<div class="row">${cards}${cardSobras}</div>`;
}

// -----------------------------------------------
// Função principal: injeta tudo dentro de #lista-davs
// -----------------------------------------------
function dadosPapeleta(carga) {
    const route = "/vendas/papeletaDados";
    const vendedor = $("#vendedor").val().trim();

    $.ajax({
        url: url + route,
        type: "POST",
        dataType: "json",
        data: {
            carga: carga,
            vendedor: vendedor
        },

        beforeSend: function () {
            $("#lista-davs").html("<p>Carregando…</p>");
            $("#placa").val("");
            $("#motorista").val("");
        },

        success: function (result) {
            const html = renderCardsDAVComParcelasPorTipo(
                result.dados_dav || [],
                result.dados_parcela || []
            );

            $("#lista-davs").html(html);

            if (result.placa)     $("#placa").val(result.placa);
            if (result.motorista) $("#motorista").val(result.motorista);
        },

        error: function () {
            $("#lista-davs").html(
                '<p class="text-danger">Falha ao carregar os dados.</p>'
            );
        }
    });
}

// -----------------------------------------------
// Enter no campo #carga para disparar a busca
// -----------------------------------------------
$(document).on("keydown", "#carga", function (e) {
    if (e.key === "Enter") {
        e.preventDefault();
        const carga = $(this).val().trim();
        if (carga) dadosPapeleta(carga);
    }
});

// -----------------------------------------------
// Utilitários para leitura dos estados no front
// -----------------------------------------------
function obterParcelasSelecionadas() {
    return Array.from(document.querySelectorAll('input.form-check-input[type="checkbox"]:checked'))
        .map(chk => chk.getAttribute('data-parcela-id'))
        .filter(id => id !== "");
}

function obterFretesEditados() {
    return Array.from(document.querySelectorAll('input.form-control[id_dav][name="frete"]'))
        .map(inp => ({
            dav: inp.getAttribute('id_dav'),
            frete: inp.value
        }));
}

function obterCobrancasEditadas() {
    return Array.from(document.querySelectorAll('input.form-control[id_dav][name="cobranca"]'))
        .map(inp => ({
            dav: inp.getAttribute('id_dav'),
            cobranca: inp.value
        }));
}

// Agregador opcional (frete + cobrança por DAV)
function obterCustosEditados() {
    const fretes = obterFretesEditados();
    const cobrancas = obterCobrancasEditadas();
    const map = new Map();

    fretes.forEach(f => map.set(f.dav, { dav: f.dav, frete: f.frete, cobranca: "" }));
    cobrancas.forEach(c => {
        if (!map.has(c.dav)) map.set(c.dav, { dav: c.dav, frete: "", cobranca: c.cobranca });
        else map.get(c.dav).cobranca = c.cobranca;
    });

    return Array.from(map.values());
}
//**************************papeleta****************************************************

/************************ atualiza-papeleta ************************************************/
    function atualiza_papeleta(){
        const route = "/vendas/atualiza_papeleta";
        const carga = $(document).find('#carga').val();
        const placa = $(document).find('#placa').val();
        const motorista = $(document).find('#motorista').val();
        $.ajax({
            url: url + route, // usa sua var global 'url'
            type: "POST",
            dataType: "json",
            data : {
                'carga'     : carga
                ,'placa'    : placa
                ,'motorista': motorista
            }
        })

    }
/************************ atualiza-papeleta-dav ************************************************/
    function atualiza_papeleta_frete(id_dav){
        let route     = "/vendas/atualiza_papeleta_frete";
        let carga     = $(document).find('#carga').val();
        let frete     = $(document).find('#frete-dav-'+id_dav).val();
        let cobranca  = $(document).find('#cobranca-dav-'+id_dav).val();
        let data = {
                'id_dav'    : id_dav
                ,'frete'    : frete
                ,'cobranca' : cobranca
            }
        $.ajax({
            url: url + route, // usa sua var global 'url'
            type: "POST",
            dataType: "json",
            data: data
        })

    }

/************************ atualiza-papeleta-parcela ************************************************/
    function atualiza_papeleta_parcela(parcela_id,check){
        let route     = "/vendas/atualiza_papeleta_parcela";

        let data = {
                'parcela_id'    : parcela_id
                ,'considera'    : check
            }
        $.ajax({
            url: url + route, // usa sua var global 'url'
            type: "POST",
            dataType: "json",
            data: data
        })

    }
