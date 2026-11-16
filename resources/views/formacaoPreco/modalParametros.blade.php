@php
    // Campo percentual do modal (mesmos ids/names de antes; o JS e o controller dependem deles).
    $campoParametro = function (string $id, string $label, $valor, string $dica, string $col = 'col-md-4', bool $autofocus = false) {
        $v = number_format((float) ($valor ?? 0), 2, ',', '.');
        $af = $autofocus ? 'autofocus' : '';

        return <<<HTML
        <div class="form-group {$col}">
            <label for="{$id}" class="mb-0 negrito">{$label}</label>
            <div class="input-group input-group-sm">
                <input class="form-control alinhaDireita atualiza_md_parametro fonte-10" type="text" name="{$id}" id="{$id}"
                    title="{$dica}" value="{$v}" inputmode="decimal" pattern="[0-9]*[,.]?[0-9]*" autocomplete="off" {$af}>
                <div class="input-group-append"><span class="input-group-text">%</span></div>
            </div>
            <small class="form-text text-muted">{$dica}</small>
        </div>
HTML;
    };
    $anoAtual = (int) date('Y');
    $anoForcado = $parametro->ibs_cbs_ano ?? '';
@endphp

<style>
    #ModalParamentros .grupo-parametros { border: 1px solid #dee2e6; border-radius: .25rem; margin-bottom: .75rem; }
    #ModalParamentros .grupo-parametros .grupo-titulo { background: #f1f4f8; padding: .35rem .75rem; font-weight: 600; border-bottom: 1px solid #dee2e6; }
    #ModalParamentros .grupo-parametros .grupo-corpo { padding: .6rem .75rem 0; }
    #ModalParamentros .grupo-reforma { border-color: #17a2b8; }
    #ModalParamentros .grupo-reforma .grupo-titulo { background: #e3f6f9; color: #0c5460; }
    #ModalParamentros .form-text { font-size: 9px; line-height: 1.2; min-height: 22px; }
    #ModalParamentros .rt-resumo td, #ModalParamentros .rt-resumo th { padding: .15rem .4rem; }
    #ModalParamentros .badge-extinto { font-size: 9px; }
</style>

<div class="modal fade ModalParamentros" id="ModalParamentros" tabindex="-1" role="dialog" aria-labelledby="ModalParamentrosLabel">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content fonte-10">

            <div class="panel panel-primary">
                <div class="panel-heading bg-primary text-white">
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>

                    <h4 class="modal-title fonte-16" id="ModalParamentrosLabel">
                        <i class="fas fa-donate"></i> Parâmetros da Formação de Preço
                    </h4>
                </div>
            </div>

            <div class="modal-body">
                <form id="form_modal_parametros">
                    @csrf

                    {{-- 1. Margem e financeiro --}}
                    <div class="grupo-parametros">
                        <div class="grupo-titulo"><i class="fas fa-chart-line text-primary"></i> Margem e financeiro</div>
                        <div class="grupo-corpo"><div class="row">
                            {!! $campoParametro('md_margem', 'Margem', $parametro->margem ?? 0, 'Margem de lucro desejada sobre o preço de venda.', 'col-md-3', true) !!}
                            {!! $campoParametro('md_parametro_taxa_financeira', 'Taxa financeira', $parametro->taxa_financeira ?? 0, 'Taxa mensal; o custo financeiro cresce conforme o prazo médio.', 'col-md-3') !!}
                            {!! $campoParametro('md_parametro_ir_csll', 'IR/CSLL sobre o lucro', $parametro->ir_csll ?? 0, 'Aplicado sobre o resultado antes do IR/CSLL.', 'col-md-3') !!}
                        </div></div>
                    </div>

                    {{-- 2. Tributos atuais --}}
                    <div class="grupo-parametros">
                        <div class="grupo-titulo">
                            <i class="fas fa-file-invoice-dollar text-warning"></i> Tributos atuais (sobre a venda)
                            <span class="badge badge-warning badge-extinto d-none ml-2" id="md_aviso_pis_cofins">PIS/COFINS extintos neste ano: não entram no preço</span>
                            <span class="badge badge-warning badge-extinto d-none ml-2" id="md_aviso_icms">ICMS reduzido neste ano</span>
                        </div>
                        <div class="grupo-corpo"><div class="row">
                            {!! $campoParametro('md_parametro_icms', 'ICMS', $parametro->icms ?? 0, 'Alíquota cheia de ICMS (reduzida automaticamente de 2029 a 2032).', 'col-md-2') !!}
                            {!! $campoParametro('md_parametro_pis', 'PIS', $parametro->pis ?? 0, 'Extinto a partir de 2027 (substituído pela CBS).', 'col-md-2') !!}
                            {!! $campoParametro('md_parametro_cofins', 'COFINS', $parametro->cofins ?? 0, 'Extinto a partir de 2027 (substituído pela CBS).', 'col-md-2') !!}
                            {!! $campoParametro('md_parametro_simples', 'Simples Nacional', $parametro->simples ?? 0, 'Alíquota efetiva do Simples (DAS), se optante.', 'col-md-3') !!}
                            {!! $campoParametro('md_parametro_difal', 'DIFAL', $parametro->difal ?? 0, 'Diferencial de alíquota, tratado como despesa variável.', 'col-md-3') !!}
                        </div></div>
                    </div>

                    {{-- 3. Reforma tributária --}}
                    <div class="grupo-parametros grupo-reforma">
                        <div class="grupo-titulo">
                            <i class="fas fa-balance-scale"></i> Reforma tributária — IBS e CBS
                            <span class="badge badge-info ml-2" id="md_rt_fase">—</span>
                        </div>
                        <div class="grupo-corpo">
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="md_parametro_ibs_cbs_ano" class="mb-0 negrito">Ano de vigência</label>
                                    <select class="form-control form-control-sm fonte-10" name="md_parametro_ibs_cbs_ano" id="md_parametro_ibs_cbs_ano">
                                        <option value="" {{ $anoForcado === '' || $anoForcado === null ? 'selected' : '' }}>Automático (ano atual: {{ $anoAtual }})</option>
                                        @for ($a = 2026; $a <= 2033; $a++)
                                            <option value="{{ $a }}" {{ (string) $anoForcado === (string) $a ? 'selected' : '' }}>{{ $a }}</option>
                                        @endfor
                                    </select>
                                    <small class="form-text text-muted">Simule o preço em outro ano da transição.</small>
                                </div>
                                {!! $campoParametro('md_parametro_cbs', 'CBS (alíquota de referência)', $parametro->cbs ?? 0, 'Alíquota cheia da CBS (federal); definida em lei. Usada a partir de 2027.', 'col-md-3') !!}
                                {!! $campoParametro('md_parametro_ibs', 'IBS (alíquota de referência)', $parametro->ibs ?? 0, 'Alíquota cheia do IBS (estados + municípios), fixada pelo Senado. Entra gradualmente de 2029 a 2033.', 'col-md-3') !!}
                                {!! $campoParametro('md_parametro_ibs_cbs_credito', 'Crédito na compra', $parametro->ibs_cbs_credito ?? 0, '% do custo de materiais que gera crédito de IBS/CBS (0 = custo já líquido).', 'col-md-3') !!}
                            </div>

                            <div class="alert alert-info py-2 mb-2" id="md_rt_texto"></div>

                            <table class="table table-sm table-bordered rt-resumo mb-2">
                                <thead class="thead-light">
                                    <tr>
                                        <th>No ano <span id="md_rt_ano_label"></span></th>
                                        <th class="text-right">CBS no preço</th>
                                        <th class="text-right">IBS no preço</th>
                                        <th class="text-right">Total IBS+CBS</th>
                                        <th class="text-right">ICMS aplicado</th>
                                        <th class="text-right">PIS+COFINS aplicado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Alíquotas efetivas</td>
                                        <td class="text-right" id="md_rt_cbs">0,00%</td>
                                        <td class="text-right" id="md_rt_ibs">0,00%</td>
                                        <td class="text-right negrito" id="md_rt_total">0,00%</td>
                                        <td class="text-right" id="md_rt_icms">0,00%</td>
                                        <td class="text-right" id="md_rt_piscofins">0,00%</td>
                                    </tr>
                                </tbody>
                            </table>
                            <small class="form-text text-muted mb-2">
                                Base legal: EC 132/2023 e LC 214/2025. As alíquotas de referência são editáveis porque dependem de resolução do Senado.
                                Com IBS/CBS zerados o cálculo permanece igual ao anterior.
                            </small>
                        </div>
                    </div>

                    {{-- 4. Despesas e comissão --}}
                    <div class="grupo-parametros">
                        <div class="grupo-titulo"><i class="fas fa-truck text-success"></i> Despesas e comissão</div>
                        <div class="grupo-corpo"><div class="row">
                            {!! $campoParametro('md_comissao', 'Comissão', $parametro->comissao ?? 0, 'Despesa variável sobre o preço de venda.', 'col-md-3') !!}
                            {!! $campoParametro('md_parametro_frete_percentual', 'Frete', $parametro->frete ?? 0, 'Frete como % do preço de venda.', 'col-md-3') !!}
                            {!! $campoParametro('md_parametro_despesa_fixa', 'Despesa fixa', $parametro->despesa_fixa ?? 0, 'Despesa fixa/indireta rateada no preço.', 'col-md-3') !!}
                            {!! $campoParametro('md_parametro_outros', 'Outros', $parametro->outros ?? 0, 'Outras despesas variáveis.', 'col-md-3') !!}
                        </div></div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <div class="row w-100">

                    <div class="form-group col-md-4 mb-0">
                        <button type="button" class="btn btn-success btn-block btn-sm fonte-10" id="md_grava" data-dismiss="modal">
                            <i class="fa fa-check"></i> Gravar padrão
                        </button>
                    </div>

                    <div class="form-group col-md-4 mb-0">
                        <button type="button" class="btn btn-info btn-block btn-sm fonte-10" id="md_simular" data-dismiss="modal">
                            <i class="fas fa-calculator"></i> Simular
                        </button>
                    </div>

                    <div class="form-group col-md-4 mb-0">
                        <button type="button" class="btn btn-danger btn-block btn-sm fonte-10" data-dismiss="modal">
                            <i class="fa fa-times"></i> Fechar
                        </button>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>
