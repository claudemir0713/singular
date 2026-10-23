(function($){'use strict';
var D={dados:null,url:'',carregando:false,numero:function(v){v=parseFloat(v);return isNaN(v)?0:v;},moeda:function(v){return new Intl.NumberFormat('pt-BR',{style:'currency',currency:'BRL'}).format(D.numero(v));},inteiro:function(v){return new Intl.NumberFormat('pt-BR',{maximumFractionDigits:0}).format(D.numero(v));},percentual:function(v){return new Intl.NumberFormat('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2}).format(D.numero(v))+'%';},data:function(v){if(!v)return '';var t=String(v).substring(0,10),p=t.split('-');return p.length===3?p[2]+'/'+p[1]+'/'+p[0]:t;},escape:function(v){return $('<div>').text(v==null?'':String(v)).html();},texto:function(s,v){$(s).text(v);},
iniciar:function(){var a=$('#dashboard-app');if(!a.length)return;D.url=a.data('url');D.dados=window.dashboardInitialData||{};D.eventos();D.renderizar(D.dados);},
eventos:function(){$(document).on('submit','#dashboard-filter-form',function(e){e.preventDefault();D.atualizar($(this).serialize());});$(document).on('click','#dashboard-clear-filter',function(){$('[name="rep_codigo"],[name="part_codigo"]').val('');$('#dashboard-filter-form').trigger('submit');});},
atualizar:function(q){if(D.carregando)return;D.carregando=true;$('#dashboard-loading').removeClass('d-none');$('#dashboard-content').addClass('dashboard-loading-content');$.get(D.url,q).done(function(r){if(r&&r.success){D.renderizar(r.dashboard);if(history.replaceState)history.replaceState({},document.title,location.pathname+'?'+q);}else alert((r&&r.message)||'Resposta inválida.');}).fail(function(x){alert((x.responseJSON&&x.responseJSON.message)||'Não foi possível atualizar o dashboard.');}).always(function(){D.carregando=false;$('#dashboard-loading').addClass('d-none');$('#dashboard-content').removeClass('dashboard-loading-content');});},
renderizar:function(d){D.dados=d||{};var f=d.financeiro||{},c=d.comercial||{},i=d.inadimplencia||{},p=d.previsao_caixa||{};D.texto('#card-vendas',D.moeda(c.VENDAS_PERIODO));D.texto('#card-receber',D.moeda(f.TOTAL_RECEBER_DUPLICATAS_SEM_ENCARGOS));D.texto('#card-cheques',D.moeda(f.TOTAL_RECEBER_CHEQUES_SEM_ENCARGOS));D.texto('#card-pagar',D.moeda(f.TOTAL_PAGAR));D.texto('#card-saldo',D.moeda(f.SALDO_PROJETADO_ORIGINAL));$('.card-saldo').toggleClass('card-saldo-negativo',D.numero(f.SALDO_PROJETADO_ORIGINAL)<0);D.texto('#card-pendentes',D.moeda(c.VALOR_PEDIDOS_PENDENTES));D.texto('#card-ticket',D.moeda(c.TICKET_MEDIO));D.texto('#card-inadimplencia',D.percentual(i.PERCENTUAL_INADIMPLENCIA));D.texto('#card-clientes',D.inteiro(c.CLIENTES_ATENDIDOS));D.texto('#detail-receber','Duplicatas sem juros e multas');D.texto('#detail-cheques','Cheques em carteira, sem juros e multas');D.texto('#detail-pagar',D.moeda(f.TOTAL_PAGAR_VENCIDO)+' vencidos');D.texto('#detail-pendentes',D.inteiro(c.QTD_PEDIDOS_PENDENTES)+' pedidos');D.texto('#detail-ticket',D.inteiro(c.QTD_PEDIDOS_VALIDOS)+' pedidos válidos');D.texto('#detail-inadimplencia',D.moeda(i.SALDO_VENCIDO)+' vencidos');['HOJE','7_DIAS','30_DIAS'].forEach(function(s){var id=s==='HOJE'?'hoje':(s==='7_DIAS'?'7':'30');D.texto('#caixa-receber-'+id,D.moeda(p['RECEBER_'+s]));D.texto('#caixa-pagar-'+id,D.moeda(p['PAGAR_'+s]));D.texto('#caixa-saldo-'+id,D.moeda(p['SALDO_'+s]));D.texto('#caixa-origem-'+id,'Duplicatas '+D.moeda(p['RECEBER_'+s+'_DUPLICATAS'])+' + Cheques '+D.moeda(p['RECEBER_'+s+'_CHEQUES']));});D.linha('grafico-evolucao',((d.evolucao_vendas||{}).dados)||[]);D.rosca('grafico-pagamentos',d.tipos_pagamento||[]);D.barras('grafico-representantes',d.vendas_representante||[],'REPRESENTANTE','VALOR_VENDIDO');D.barras('grafico-situacoes',d.situacoes_pedidos||[],'SITUACAO','VALOR_PEDIDOS');D.tabelas(d);},
linha:function(id,a){var e=document.getElementById(id);if(!e)return;if(!a.length){e.innerHTML='<div class="dashboard-empty">Sem dados para o período.</div>';return;}var w=800,h=250,m=35,max=Math.max.apply(null,a.map(function(x){return D.numero(x.VALOR_VENDIDO);}).concat([1]));var pts=a.map(function(x,i){return {x:m+i*((w-2*m)/Math.max(a.length-1,1)),y:h-m-D.numero(x.VALOR_VENDIDO)/max*(h-2*m),r:x.ROTULO||x.PERIODO};});e.innerHTML='<svg class="dashboard-svg" viewBox="0 0 800 250"><line x1="35" y1="215" x2="765" y2="215"/><polyline class="svg-line" points="'+pts.map(function(p){return p.x+','+p.y;}).join(' ')+'"/>'+pts.map(function(p){return '<circle cx="'+p.x+'" cy="'+p.y+'" r="4"/><text x="'+p.x+'" y="242" text-anchor="middle">'+D.escape(p.r)+'</text>';}).join('')+'</svg>';},
rosca:function(id,a){var e=document.getElementById(id);if(!e)return;if(!a.length){e.innerHTML='<div class="dashboard-empty">Sem dados.</div>';return;}var t=a.reduce(function(s,x){return s+D.numero(x.SALDO_RECEBER);},0),cores=['#08b8b5','#249db7','#ee9d2d','#756ac4','#25a66a','#b8c2c9'],ac=0,g=a.map(function(x,i){var z=ac;ac+=t?D.numero(x.SALDO_RECEBER)/t*100:0;return cores[i%cores.length]+' '+z+'% '+ac+'%';}).join(',');e.innerHTML='<div class="svg-donut" style="background:conic-gradient('+g+')"><span>'+D.moeda(t)+'</span></div><ul class="svg-legend">'+a.map(function(x,i){return '<li><i style="background:'+cores[i%cores.length]+'"></i><span>'+D.escape(x.TIPO_PAGAMENTO)+'</span><b>'+D.percentual(x.PERCENTUAL)+'</b></li>';}).join('')+'</ul>';},
barras:function(id,a,l,v){var e=document.getElementById(id);if(!e)return;if(!a.length){e.innerHTML='<div class="dashboard-empty">Sem dados.</div>';return;}var max=Math.max.apply(null,a.map(function(x){return D.numero(x[v]);}).concat([1]));e.innerHTML='<div class="svg-bars">'+a.slice(0,10).map(function(x){return '<div class="svg-bar-row"><span>'+D.escape(x[l])+'</span><div><i style="width:'+(D.numero(x[v])/max*100)+'%"></i></div><b>'+D.moeda(x[v])+'</b></div>';}).join('')+'</div>';},
tabelas:function(d){function fill(s,a,n,fn){$(s).html(a.length?a.map(fn).join(''):'<tr><td colspan="'+n+'" class="text-center">Nenhum registro.</td></tr>');}fill('#tbody-titulos',d.titulos_vencidos||[],7,function(x){return '<tr><td>'+D.escape(x.ORIGEM)+'</td><td>'+D.escape(x.CLIENTE)+'</td><td>'+D.escape(x.CON_NUMERO)+'</td><td>'+D.escape(x.CON_SEQUENCIA)+'</td><td>'+D.data(x.CON_DT_VENCIMENTO)+'</td><td>'+D.escape(x.TIPO_PAGAMENTO)+'</td><td class="text-right">'+D.moeda(x.SALDO_ATUAL)+'</td></tr>';});fill('#tbody-clientes',d.clientes||[],4,function(x){return '<tr><td>'+D.escape(x.CLIENTE)+'</td><td class="text-right">'+D.inteiro(x.QTD_PEDIDOS)+'</td><td class="text-right">'+D.moeda(x.TICKET_MEDIO)+'</td><td class="text-right">'+D.moeda(x.VALOR_VENDIDO)+'</td></tr>';});fill('#tbody-produtos',d.produtos||[],5,function(x){return '<tr><td>'+D.escape(x.PRD_CODIGO)+'</td><td>'+D.escape(x.PRODUTO)+'</td><td class="text-right">'+D.inteiro(x.QUANTIDADE_VENDIDA)+'</td><td class="text-right">'+D.moeda(x.PRECO_MEDIO)+'</td><td class="text-right">'+D.moeda(x.VALOR_VENDIDO)+'</td></tr>';});}
};window.DashboardExecutivo=D;$(D.iniciar);
})(jQuery);
(function ($) {
    'use strict';

    function numero(valor) {
        valor = parseFloat(valor);
        return isNaN(valor) ? 0 : valor;
    }

    function moeda(valor) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(numero(valor));
    }

    function inteiro(valor) {
        return new Intl.NumberFormat('pt-BR', {
            maximumFractionDigits: 0
        }).format(numero(valor));
    }

    function escapeHtml(valor) {
        return $('<div>').text(valor === null || valor === undefined ? '' : String(valor)).html();
    }

    function dadosDashboard() {
        if (window.DashboardExecutivo && window.DashboardExecutivo.dados) {
            return window.DashboardExecutivo.dados;
        }

        return window.dashboardInitialData || {};
    }

    function filtrosMemoria(memoria) {
        var filtros = memoria && memoria.filtros ? memoria.filtros : {};
        var regras = [];

        if (filtros.vencimento_inicial && filtros.vencimento_final) {
            regras.push(['Período financeiro', filtros.vencimento_inicial + ' até ' + filtros.vencimento_final]);
        } else if (filtros.data_inicial && filtros.data_final) {
            regras.push(['Período', filtros.data_inicial + ' até ' + filtros.data_final]);
        }

        if (filtros.rep_codigo) {
            regras.push(['Representante', 'PART_REPRESENTANTE_CODIGO = ' + filtros.rep_codigo]);
        }

        if (filtros.part_codigo) {
            regras.push(['Cliente', 'PART_CODIGO = ' + filtros.part_codigo]);
        }

        if (filtros.usuario_restrito) {
            regras.push(['Acesso', 'Usuário restrito pelo representante vinculado']);
        }

        return regras;
    }

    function filtrosMemoriaSemPeriodo(memoria) {
        var filtros = memoria && memoria.filtros ? memoria.filtros : {};
        var regras = [];

        if (filtros.rep_codigo) {
            regras.push(['Representante', 'PART_REPRESENTANTE_CODIGO = ' + filtros.rep_codigo]);
        }

        if (filtros.part_codigo) {
            regras.push(['Cliente', 'PART_CODIGO = ' + filtros.part_codigo]);
        }

        if (filtros.usuario_restrito) {
            regras.push(['Acesso', 'Usuário restrito pelo representante vinculado']);
        }

        return regras;
    }

    function linha(rotulo, valor, tipo, numeroBruto) {
        return [rotulo, valor, tipo || '', numeroBruto];
    }
    function linhaValor(rotulo, valor, tipo) {
        return linha(rotulo, moeda(valor), tipo, numero(valor));
    }
    function valorNaoZero(valor) {
        return Math.abs(numero(valor)) > 0.000001;
    }

    function memoriaReceber(dados) {
        var f = dados.financeiro || {};
        var memoria = (dados.memoria_calculo || {}).receber || {};
        var duplicatasSemEncargos = numero(f.TOTAL_RECEBER_DUPLICATAS_SEM_ENCARGOS);
        var duplicatasComEncargos = duplicatasSemEncargos + numero(f.RECEBER_JUROS_MULTA);
        return {
            titulo: 'Duplicatas a receber',
            formula: 'VALOR ORIGINAL - PAGAMENTOS - CREDITOS - DESCONTOS + OUTRAS DESPESAS',
            regras: (memoria.regras || []).concat(['Cheques apresentados em card e memoria separados', 'O valor do card nao considera juros nem multas']),
            resumo: [
                ['Duplicatas sem juros e multas', moeda(duplicatasSemEncargos), duplicatasSemEncargos],
                ['Juros + multas', moeda(f.RECEBER_JUROS_MULTA), numero(f.RECEBER_JUROS_MULTA)],
                ['Duplicatas + juros/multas', moeda(duplicatasComEncargos), duplicatasComEncargos]
            ],
            colunas: [
                { titulo: 'DUPLICATAS', linhas: [
                    linhaValor('Valor original dos titulos', f.RECEBER_VALOR_ORIGINAL),
                    linhaValor('(-) Pagamentos', f.RECEBER_VALOR_PAGO, 'menos'),
                    linhaValor('(-) Creditos', f.TOTAL_RECEBER_CREDITOS, 'menos'),
                    linhaValor('(-) Descontos', f.RECEBER_VALOR_DESCONTO, 'menos'),
                    linhaValor('(+) Outras despesas', f.RECEBER_OUTRAS_DESPESAS, 'mais'),
                    linhaValor('Saldo sem juros e multas', duplicatasSemEncargos, 'subtotal')
                ]},
                { titulo: 'ACRESCIMOS', linhas: [
                    linhaValor('(+) Juros', f.RECEBER_VALOR_JUROS, 'mais'),
                    linhaValor('(+) Multas', f.RECEBER_VALOR_MULTA, 'mais'),
                    linhaValor('Juros + multas', f.RECEBER_JUROS_MULTA, 'subtotal'),
                    linhaValor('Duplicatas + juros/multas', duplicatasComEncargos, 'total')
                ]}
            ],
            calculo: []
        };
    }

    function memoriaCheques(dados) {
        var f = dados.financeiro || {};
        var periodo = dados.periodo || {};
        var total = numero(f.TOTAL_RECEBER_CHEQUES_SEM_ENCARGOS);
        return {
            titulo: 'Cheques a receber',
            formula: 'SUM(CHQ_VALOR) DOS CHEQUES EM CARTEIRA',
            regras: [
                'CHQ_TERC = 0',
                'CHQ_SITUACAO = 0',
                'CHQ_BOM_PARA entre ' + formatarDataMemoria(periodo.vencimento_inicial) + ' e ' + formatarDataMemoria(periodo.vencimento_final),
                'Conta vinculada a FIN_CONTAS com CON_TIPO = 0',
                'Cheques separados das duplicatas',
                'O valor do card nao considera juros nem multas'
            ],
            resumo: [
                ['Cheques sem juros e multas', moeda(total), total],
                ['Juros considerados no card', moeda(0), 0],
                ['Multas consideradas no card', moeda(0), 0]
            ],
            colunas: [
                { titulo: 'CARTEIRA DE CHEQUES', linhas: [
                    linhaValor('Cheques vinculados em carteira', total),
                    linhaValor('Saldo sem juros e multas', total, 'total')
                ]},
                { titulo: 'REGRA DO CARD', linhas: [
                    linhaValor('Juros considerados', 0),
                    linhaValor('Multas consideradas', 0),
                    linhaValor('Valor exibido no card', total, 'total')
                ]}
            ],
            calculo: []
        };
    }

    function memoriaSaldo(dados) {
        var f = dados.financeiro || {};
        var memoria = (dados.memoria_calculo || {}).saldo || {};
        return {
            titulo: 'Saldo projetado',
            formula: 'ORIGINAL MENOS PAGAMENTOS - CONTAS A PAGAR',
            resumo: [
                ['Saldo projetado', moeda(f.SALDO_PROJETADO_ORIGINAL)],
                ['Original menos pagamentos', moeda(f.RECEBER_SALDO_ORIGINAL_MENOS_PAGAMENTOS)],
                ['Total a pagar', moeda(f.TOTAL_PAGAR)]
            ],
            regras: [
                ['Receber', 'Valor original menos pagamentos'],
                ['Pagar', 'CON_TIPO = 1; CON_PREVISAO = 0'],
                ['Resultado', 'O card fica vermelho somente quando o saldo é negativo']
            ].concat(filtrosMemoriaSemPeriodo(memoria)),
            calculo: [
                linha('RESULTADO', '', 'grupo'),
                linha('Original menos pagamentos', moeda(f.RECEBER_SALDO_ORIGINAL_MENOS_PAGAMENTOS)),
                linha('(-) Contas a pagar', moeda(f.TOTAL_PAGAR)),
                linha('Saldo projetado', moeda(f.SALDO_PROJETADO_ORIGINAL), 'total')
            ]
        };
    }

    function memoriaInadimplencia(dados) {
        var inadimplencia = dados.inadimplencia || {};
        var memoria = (dados.memoria_calculo || {}).inadimplencia || {};

        return {
            titulo: 'Inadimplência',
            formula: memoria.formula || 'TÍTULOS VENCIDOS E NÃO PAGOS / TODOS OS TÍTULOS COM VENCIMENTO NO PERÍODO * 100',
            resumo: [
                ['Percentual', numero(inadimplencia.PERCENTUAL_INADIMPLENCIA).toFixed(2).replace('.', ',') + '%'],
                ['Saldo vencido', moeda(inadimplencia.SALDO_VENCIDO)],
                ['Saldo do período', moeda(inadimplencia.SALDO_TOTAL_PERIODO)]
            ],
            regras: [
                ['Base do período', 'Todos os títulos com vencimento no período, pagos ou não'],
                ['Duplicata inadimplente', 'CON_DT_VENCIMENTO <= CURRENT_DATE; CON_SITUACAO IN (0, 1); saldo > 0'],
                ['Cheque inadimplente', 'CHQ_BOM_PARA <= CURRENT_DATE; CHQ_SITUACAO = 0'],
                ['Duplicatas pagas', 'Entram somente na base pelo CON_VALOR_ORIGINAL'],
                ['Cheques pagos', 'Entram somente na base pelo CHQ_VALOR']
            ].concat(filtrosMemoria(memoria)),
            calculo: [
                linha('VALORES VENCIDOS', '', 'grupo'),
                linha('Duplicatas vencidas', moeda(inadimplencia.SALDO_VENCIDO_DUPLICATAS)),
                linha('Cheques vencidos', moeda(inadimplencia.SALDO_VENCIDO_CHEQUES)),
                linha('Total vencido', moeda(inadimplencia.SALDO_VENCIDO), 'total'),
                linha('BASE DO PERÍODO', '', 'grupo'),
                linha('Todas as duplicatas do período', moeda(inadimplencia.SALDO_TOTAL_DUPLICATAS)),
                linha('Todos os cheques do período', moeda(inadimplencia.SALDO_TOTAL_CHEQUES)),
                linha('Todos os títulos do período', moeda(inadimplencia.SALDO_TOTAL_PERIODO), 'total'),
                linha('RESULTADO', '', 'grupo'),
                linha('Percentual de inadimplência', numero(inadimplencia.PERCENTUAL_INADIMPLENCIA).toFixed(2).replace('.', ',') + '%', 'total')
            ]
        };
    }

    function faixaCaixa(previsao, sufixo, titulo) {
        return [
            linha(titulo, '', 'grupo'),
            linha('Duplicatas a receber', moeda(previsao['RECEBER_' + sufixo + '_DUPLICATAS'])),
            linha('Cheques a receber', moeda(previsao['RECEBER_' + sufixo + '_CHEQUES'])),
            linha('Total a receber', moeda(previsao['RECEBER_' + sufixo])),
            linha('Contas a pagar', moeda(previsao['PAGAR_' + sufixo])),
            linha('Saldo projetado', moeda(previsao['SALDO_' + sufixo]), 'total')
        ];
    }

    function memoriaCaixa(dados) {
        var previsao = dados.previsao_caixa || {};
        var memoria = (dados.memoria_calculo || {}).caixa || {};

        return {
            titulo: 'Previsão de caixa',
            formula: memoria.formula || '(DUPLICATAS + CHEQUES) - CONTAS_A_PAGAR por faixa de vencimento',
            resumo: [
                ['Hoje', moeda(previsao.SALDO_HOJE)],
                ['7 dias', moeda(previsao.SALDO_7_DIAS)],
                ['30 dias', moeda(previsao.SALDO_30_DIAS)]
            ],
            regras: [
                ['Duplicatas', 'Data considerada: CON_DT_VENCIMENTO'],
                ['Cheques', 'Data considerada: CHQ_BOM_PARA'],
                ['Hoje', 'DATA_VENCIMENTO = CURRENT_DATE'],
                ['7 dias', 'DATA_VENCIMENTO > CURRENT_DATE E <= CURRENT_DATE + 7'],
                ['30 dias', 'DATA_VENCIMENTO > CURRENT_DATE E <= CURRENT_DATE + 30']
            ].concat(filtrosMemoria(memoria)),
            calculo: []
                .concat(faixaCaixa(previsao, 'HOJE', 'HOJE'))
                .concat(faixaCaixa(previsao, '7_DIAS', 'PRÓXIMOS 7 DIAS'))
                .concat(faixaCaixa(previsao, '30_DIAS', 'PRÓXIMOS 30 DIAS'))
        };
    }

    function renderizarMemoria(memoria) {
        $('#memory-title').text('Memória de cálculo: ' + memoria.titulo);
        var resumo = (memoria.resumo || []).filter(function (item) {
            return item[2] === null || item.length < 3 || valorNaoZero(item[2]);
        });
        $('#memory-summary').html(resumo.map(function (item) {
            return '<div class="memory-summary-item"><small>' + escapeHtml(item[0]) + '</small><strong>' + item[1] + '</strong></div>';
        }).join(''));
        $('#memory-rules').html((memoria.regras || []).map(function (item, indice) {
            var titulo = 'Regra ' + (indice + 1);
            var descricao = item;
            if (Array.isArray(item)) {
                titulo = item[0] || titulo;
                descricao = item.length > 1 ? item[1] : item[0];
            } else if (item && typeof item === 'object') {
                titulo = item.titulo || item.nome || titulo;
                descricao = item.descricao || item.regra || item.valor || '';
            }
            return '<li><strong>' + escapeHtml(titulo) + '</strong><span class="memory-rule-text">' + escapeHtml(descricao) + '</span></li>';
        }).join(''));
        if (memoria.colunas && memoria.colunas.length) {
            $('#memory-calc').html('<div class="memory-columns">' + memoria.colunas.map(function (coluna) {
                var linhas = (coluna.linhas || []).filter(function (item) {
                    return item[2] === 'total' || item[2] === 'subtotal' || item.length < 4 || valorNaoZero(item[3]);
                });
                return '<section class="memory-column"><h4>' + escapeHtml(coluna.titulo) + '</h4><div>' + linhas.map(function (item) {
                    var tipo = item[2] || 'normal';
                    return '<div class="memory-line memory-line-' + escapeHtml(tipo) + '"><span>' + escapeHtml(item[0]) + '</span><strong>' + item[1] + '</strong></div>';
                }).join('') + '</div></section>';
            }).join('') + '</div>');
        } else {
            $('#memory-calc').html('<table class="table memory-calc-table"><tbody>' + (memoria.calculo || []).map(function (item) {
                var tipo = item[2] || '';
                if (tipo === 'grupo') return '<tr class="memory-calc-group"><td colspan="2">' + escapeHtml(item[0]) + '</td></tr>';
                return '<tr class="' + (tipo === 'total' ? 'memory-calc-total' : '') + '"><td>' + escapeHtml(item[0]) + '</td><td class="text-right">' + item[1] + '</td></tr>';
            }).join('') + '</tbody></table>');
        }
        $('#memory-formula').text(memoria.formula || '');
        $('#dashboardMemoryModal').modal('show');
    }

    function abrirMemoriaEspecial(chave) {
        var dados = dadosDashboard();
        var memoria = null;

        if (chave === 'receber') memoria = memoriaReceber(dados);
        if (chave === 'cheques') memoria = memoriaCheques(dados);
        if (chave === 'saldo') memoria = memoriaSaldo(dados);
        if (chave === 'inadimplencia') memoria = memoriaInadimplencia(dados);
        if (chave === 'caixa') memoria = memoriaCaixa(dados);

        if (!memoria) return false;

        renderizarMemoria(memoria);
        return true;
    }

    $(document).on('click.dashboardMemoriasFinanceiras', '.dashboard-memory-btn', function (event) {
        var chave = $(this).data('memory');

        if (abrirMemoriaEspecial(chave)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    });

    window.DashboardMemoriasFinanceiras = {
        abrir: abrirMemoriaEspecial,
        renderizar: renderizarMemoria
    };
})(jQuery);
(function($){'use strict';
function formatarDataMemoria(valor){if(!valor)return '';var texto=String(valor).substring(0,10),partes=texto.split('-');return partes.length===3?partes[2]+'/'+partes[1]+'/'+partes[0]:texto;}
function formatarRotuloMemoria(chave){var mapa={vencido:'Vencido',a_vencer:'A vencer',total:'Total',quantidade:'Quantidade',valor:'Valor',vendas:'Vendas',pedidos:'Pedidos',ticket:'Ticket médio',clientes:'Clientes',valor_periodo:'Valor do período',quantidade_pedidos:'Quantidade de pedidos',valor_periodo_anterior:'Valor do período anterior',quantidade_exibida:'Quantidade exibida'};return mapa[chave]||chave.replace(/_/g,' ').replace(/^./,function(x){return x.toUpperCase();});}
function formatarValorMemoria(chave,valor){var k=String(chave).toLowerCase();if(typeof valor==='string'&&/^\d{4}-\d{2}-\d{2}/.test(valor))return formatarDataMemoria(valor);if(typeof valor!=='number'&&!(typeof valor==='string'&&valor!==''&&!isNaN(valor)))return valor;var n=parseFloat(valor);if(k.indexOf('percentual')>=0)return window.DashboardExecutivo.percentual(n);if(k.indexOf('quantidade')>=0||k.indexOf('qtd')>=0||k==='pedidos'||k==='clientes')return window.DashboardExecutivo.inteiro(n);return window.DashboardExecutivo.moeda(n);}
$(document).on('click.dashboardMemoriaGenerica','.dashboard-memory-btn',function(){var chave=$(this).data('memory');if(['receber','saldo','inadimplencia','caixa'].indexOf(chave)>=0)return;var d=(window.DashboardExecutivo&&window.DashboardExecutivo.dados)||window.dashboardInitialData||{},m=(d.memoria_calculo||{})[chave];if(!m)return;var titulo=$(this).closest('.dashboard-card,.dashboard-panel').find('.dashboard-card-top span:first,.dashboard-panel-title span:first').first().text()||chave;var periodo=d.periodo||{};$('#memory-title').text('Memória de cálculo: '+titulo);$('#memory-summary').html('<div class="memory-summary-item"><small>Período</small><strong>'+formatarDataMemoria(periodo.data_inicial)+' até '+formatarDataMemoria(periodo.data_final)+'</strong></div><div class="memory-summary-item"><small>Indicador</small><strong>'+titulo+'</strong></div><div class="memory-summary-item"><small>Origem</small><strong>Dados do dashboard</strong></div>');$('#memory-formula').text(m.formula||'Cálculo conforme regras do indicador');$('#memory-rules').html((m.regras||[]).map(function(x){return '<li><strong>Regra</strong><code>'+window.DashboardExecutivo.escape(x)+'</code></li>';}).join(''));var c=m.composicao||{},linhas=[];Object.keys(c).forEach(function(k){var v=c[k];if(v!==null&&typeof v!=='object')linhas.push('<tr class="'+(k==='total'||k.indexOf('saldo')>=0?'memory-calc-total':'')+'"><td>'+window.DashboardExecutivo.escape(formatarRotuloMemoria(k))+'</td><td class="text-right">'+window.DashboardExecutivo.escape(formatarValorMemoria(k,v))+'</td></tr>');});$('#memory-calc').html(linhas.join(''));$('#dashboardMemoryModal').modal('show');});
})(jQuery);

(function ($) {
    'use strict';

    function iniciarSelectsDashboard() {
        if (!$.fn.select2) {
            return;
        }

        $('.dashboard-select').each(function () {
            var $select = $(this);

            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy');
            }

            $select.select2({
                width: '100%',
                placeholder: $select.data('placeholder') || '',
                allowClear: false,
                dropdownAutoWidth: false,
                dropdownCssClass: 'dashboard-select-dropdown',
                language: {
                    noResults: function () {
                        return 'Nenhum registro encontrado';
                    },
                    searching: function () {
                        return 'Pesquisando...';
                    }
                }
            });
        });
    }

    $(iniciarSelectsDashboard);

    $(document).on('select2:open.dashboard', '.dashboard-select', function () {
        window.setTimeout(function () {
            var campo = document.querySelector('.select2-container--open .select2-search__field');
            if (campo) {
                campo.focus();
            }
        }, 0);
    });

    window.iniciarSelectsDashboard = iniciarSelectsDashboard;
})(jQuery);


/* Atualizacao AJAX automatica. Altere somente INTERVALO_MS. */
(function($){'use strict';
var INTERVALO_MS=5000,contador=null,requisicao=null,segundos=segundosDoIntervalo();
function segundosDoIntervalo(){return Math.max(1,Math.ceil(INTERVALO_MS/1000));}
function disponivel(){return window.DashboardExecutivo&&$('#dashboard-app').length&&window.DashboardExecutivo.url;}
function memoriaAberta(){return $('#dashboardMemoryModal').hasClass('show')||$('#dashboardMemoryModal').is(':visible');}
function criar(){var h=$('.dashboard-updated').first();if(h.length&&!$('#dashboard-auto-counter').length)h.after('<div id="dashboard-auto-counter" style="margin-top:4px;color:#74838e;font-size:12px;text-align:right"><i class="fas fa-sync-alt"></i> <span id="dashboard-auto-counter-text"></span></div>');}
function mostrar(t){criar();$('#dashboard-auto-counter-text').text(t);}
function reiniciar(){segundos=segundosDoIntervalo();mostrar('Atualizacao em '+segundos+'s');}
function horario(){var a=new Date(),h=String(a.getHours()).padStart(2,'0'),m=String(a.getMinutes()).padStart(2,'0'),s=String(a.getSeconds()).padStart(2,'0');$('.dashboard-updated').first().text('Atualizado em '+h+':'+m+':'+s);}
function pode(){return disponivel()&&!document.hidden&&!memoriaAberta()&&!window.DashboardExecutivo.carregando&&(!requisicao||requisicao.readyState===4);}
function atualizar(){if(!pode())return;mostrar('Atualizando...');requisicao=$.ajax({url:window.DashboardExecutivo.url,type:'GET',data:$('#dashboard-filter-form').serialize(),dataType:'json',cache:false,timeout:Math.max(1000,INTERVALO_MS-1000),headers:{'X-Requested-With':'XMLHttpRequest'}}).done(function(r){if(r&&r.success&&r.dashboard){window.DashboardExecutivo.renderizar(r.dashboard);horario();$(document).trigger('dashboard:atualizado',[r.dashboard]);}}).fail(function(x,status){if(status!=='abort'&&window.console)console.warn('Atualizacao automatica nao concluida.');}).always(function(){requisicao=null;reiniciar();});}
function pulso(){if(document.hidden){mostrar('Atualizacao pausada');return;}if(memoriaAberta()){mostrar('Pausado durante a memoria');return;}if(requisicao&&requisicao.readyState!==4){mostrar('Atualizando...');return;}segundos--;if(segundos<=0){segundos=segundosDoIntervalo();atualizar();return;}mostrar('Atualizacao em '+segundos+'s');}
function iniciar(){parar();reiniciar();contador=window.setInterval(pulso,1000);}
function parar(){if(contador)window.clearInterval(contador);contador=null;}
function cancelar(){if(requisicao&&requisicao.readyState!==4)requisicao.abort();requisicao=null;}
$(function(){if(disponivel())iniciar();});
document.addEventListener('visibilitychange',function(){if(document.hidden){cancelar();mostrar('Atualizacao pausada');}else{reiniciar();atualizar();}});
$(document).on('show.bs.modal.dashboardAutoUpdate','#dashboardMemoryModal',function(){mostrar('Pausado durante a memoria');});
$(document).on('hidden.bs.modal.dashboardAutoUpdate','#dashboardMemoryModal',reiniciar);
$(document).on('submit.dashboardAutoUpdate','#dashboard-filter-form',function(){cancelar();reiniciar();});
$(window).on('beforeunload.dashboardAutoUpdate',function(){parar();cancelar();});
window.DashboardAutoUpdate={iniciar:iniciar,parar:parar,atualizarAgora:atualizar,reiniciarContador:reiniciar,intervalo:INTERVALO_MS,segundos:segundosDoIntervalo()};
})(jQuery);
