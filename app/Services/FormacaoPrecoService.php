<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class FormacaoPrecoService
{
    public function buscarCustoProduto(string $codigo, ?string $anoMes = null): array
    {
        $codigo = $this->normalizarCodigo($codigo);

        if ($codigo == '') {
            return $this->retornoSemCusto($codigo, 'Código do produto não informado.');
        }

        $estrutura = $this->buscarUltimoCustoEstrutura($codigo, $anoMes);



        if ($estrutura) {
            // O custo gravado é por peça (chapa). O valor por m² é só uma conversão para consulta.
            [$fator, $unidadeMed, $dimensoes] = $this->fatorUnidadeVenda($codigo, $estrutura);

            $itensEstrutura = $this->buscarEstruturaProduto(
                $codigo,
                (int) $estrutura->ano,
                (int) $estrutura->mes
            );

            $alertas = $this->montarAlertas($codigo, $estrutura, $itensEstrutura, $fator, $unidadeMed);
            $origemDetalhe = $this->descreverOrigem($estrutura);

            return [
                'codigo' => trim((string) $estrutura->cod),
                'descricao' => trim((string) $estrutura->descr),
                'ano' => (int) $estrutura->ano,
                'mes' => (int) $estrutura->mes,
                'ano_mes_custo' => trim((string) $estrutura->ano_mes),

                'tipo_item_custo' => 'PRODUTO_ACABADO',
                'origem_custo' => 'ESTRUTURA',

                'embalagem' => $estrutura->embalagem,
                'custo_percentual' => (float) $estrutura->custo_percentual,

                'unid_med' => $unidadeMed,
                'fator_unid' => $fator,
                'dimensoes' => $dimensoes,
                'alertas' => $alertas,
                'origem_detalhe' => $origemDetalhe,
                'meses_disponiveis' => $this->mesesDisponiveis($codigo),
                // Regra antiga: custo por chapa, soma todos os níveis da ficha.
                'regra_antiga' => !config('ficha.regra_nova'),

                'custo_mp_peca' => (float) $estrutura->custo_mp,
                'custo_op_peca' => (float) $estrutura->custo_op,
                'custo_total_peca' => (float) $estrutura->custo_total,

                // Custo usado na formação de preço: por peça (chapa), como nas demais competências.
                'custo_mp_unit' => (float) $estrutura->custo_mp,
                'custo_op_unit' => (float) $estrutura->custo_op,
                'custo_total_unit' => (float) $estrutura->custo_total,

                // Conversão para a unidade de venda (ex.: m²), apenas informativa.
                'custo_mp_m2' => (float) $estrutura->custo_mp / $fator,
                'custo_op_m2' => (float) $estrutura->custo_op / $fator,
                'custo_total_m2' => (float) $estrutura->custo_total / $fator,

                'estrutura' => $itensEstrutura,

                'encontrado' => true,
                'message' => 'Custo encontrado na estrutura.',
            ];
        }

        $mp = $this->buscarUltimoCustoMp($codigo, $anoMes);

        if ($mp) {
            return [
                'codigo' => trim((string) $mp->cod_mp),
                'descricao' => trim((string) $mp->desc_mp),
                'ano' => (int) $mp->ano,
                'mes' => (int) $mp->mes,
                'ano_mes_custo' => trim((string) $mp->ano_mes),

                'tipo_item_custo' => 'MATERIA_PRIMA',
                'origem_custo' => 'MP',

                'embalagem' => null,
                'custo_percentual' => 1,

                'meses_disponiveis' => $this->mesesDisponiveis($codigo),
                'regra_antiga' => false,
                'unid_med' => $mp->unid_med,
                'tipo_mp' => $mp->tipo,

                'custo_mp_unit' => (float) $mp->custo_ok,
                'custo_op_unit' => 0,
                'custo_total_unit' => (float) $mp->custo_ok,

                'encontrado' => true,
                'message' => 'Custo encontrado como matéria-prima.',
            ];
        }

        $retorno = $this->retornoSemCusto(
            $codigo,
            $anoMes
                ? 'Sem custo para este produto na competência ' . $anoMes . '.'
                : 'Custo não encontrado para o item informado.'
        );

        $retorno['meses_disponiveis'] = $this->mesesDisponiveis($codigo);
        $retorno['regra_antiga'] = false;
        $retorno['ano_mes_custo'] = $anoMes;

        return $retorno;
    }

    /**
     * Regra de transição da reforma tributária do consumo (EC 132/2023, LC 214/2025, LC 227/2026), por ano.
     * Única fonte da regra: a tela recebe esta tabela e o cálculo de preço (JS) a aplica sobre as alíquotas de
     * referência informadas em Parâmetros (as alíquotas cheias são fixadas por resolução do Senado e podem mudar).
     *
     *  - cbs_modo / ibs_modo: 'nenhum' | 'teste' (alíquota fixa, compensada, não onera o preço) | 'cheia' | 'parcial'
     *  - cbs_teste / ibs_teste: alíquota fixa de teste (%), só informativa
     *  - ibs_fator: fração da alíquota de referência do IBS cobrada no ano (modo parcial/cheia)
     *  - icms_fator / pis_cofins_fator: fração do ICMS e de PIS/COFINS ainda cobrada no ano
     */
    public function regraTransicaoTributaria(): array
    {
        $linha = function (int $ano, string $fase, string $cbs, string $ibs, float $ibsFator, float $icmsFator, float $pisCofins, string $texto, float $cbsTeste = 0, float $ibsTeste = 0) {
            return [
                'ano' => $ano, 'fase' => $fase,
                'cbs_modo' => $cbs, 'ibs_modo' => $ibs,
                'cbs_teste' => $cbsTeste, 'ibs_teste' => $ibsTeste,
                'ibs_fator' => $ibsFator, 'icms_fator' => $icmsFator, 'pis_cofins_fator' => $pisCofins,
                'texto' => $texto,
            ];
        };

        $tabela = [
            $linha(2026, 'Ano de teste', 'teste', 'teste', 0, 1, 1,
                'CBS 0,9% e IBS 0,1% apenas de teste (destaque na nota), compensados com PIS/COFINS: não oneram o preço. ICMS, PIS e COFINS seguem integrais.', 0.9, 0.1),
            $linha(2027, 'CBS plena', 'cheia', 'teste', 0, 1, 0,
                'CBS entra plena e PIS/COFINS são extintos. IBS de teste 0,1% (compensado com a CBS). ICMS integral.', 0, 0.1),
            $linha(2028, 'CBS plena', 'cheia', 'teste', 0, 1, 0,
                'CBS plena, PIS/COFINS extintos. IBS de teste 0,1% (compensado com a CBS). ICMS integral.', 0, 0.1),
            $linha(2029, 'Entrada do IBS', 'cheia', 'parcial', 0.10, 0.90, 0, 'IBS a 10% da alíquota de referência; ICMS reduzido para 90%.'),
            $linha(2030, 'Entrada do IBS', 'cheia', 'parcial', 0.20, 0.80, 0, 'IBS a 20% da alíquota de referência; ICMS reduzido para 80%.'),
            $linha(2031, 'Entrada do IBS', 'cheia', 'parcial', 0.30, 0.70, 0, 'IBS a 30% da alíquota de referência; ICMS reduzido para 70%.'),
            $linha(2032, 'Entrada do IBS', 'cheia', 'parcial', 0.40, 0.60, 0, 'IBS a 40% da alíquota de referência; ICMS reduzido para 60%.'),
            $linha(2033, 'Sistema pleno', 'cheia', 'cheia', 1, 0, 0, 'IBS e CBS plenos; ICMS (e ISS) extintos.'),
        ];

        return [
            'antes' => $linha(2025, 'Sem reforma', 'nenhum', 'nenhum', 0, 1, 1, 'Reforma ainda não vigente: ICMS, PIS e COFINS integrais.'),
            'anos' => $tabela,
        ];
    }

    /**
     * Competências (AAAA-MM) com custo para o produto, da mais recente para a mais antiga.
     */
    private function mesesDisponiveis(string $codigo): array
    {
        $estrutura = DB::table('produto_custo_estrutura_mysql')
            ->where('cod', $codigo)->pluck('ano_mes')->all();

        $mp = DB::table('produto_custos_mp_mysql')
            ->where('cod_mp', $codigo)->pluck('ano_mes')->all();

        $meses = array_values(array_unique(array_map('trim', array_merge($estrutura, $mp))));
        rsort($meses);

        return $meses;
    }

    private function buscarUltimoCustoEstrutura(string $codigo, ?string $anoMes = null)
    {
        return DB::table('produto_custo_estrutura_mysql')
            ->where('cod', $codigo)
            ->when($anoMes, function ($q) use ($anoMes) {
                $q->where('ano_mes', $anoMes);
            })
            ->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->first();
    }

    private function buscarUltimoCustoMp(string $codigo, ?string $anoMes = null)
    {
        return DB::table('produto_custos_mp_mysql')
            ->where('cod_mp', $codigo)
            ->when($anoMes, function ($q) use ($anoMes) {
                $q->where('ano_mes', $anoMes);
            })
            ->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->first();
    }

    private function retornoSemCusto(string $codigo, string $mensagem): array
    {
        return [
            'codigo' => $codigo,
            'descricao' => '',
            'ano' => null,
            'mes' => null,
            'ano_mes_custo' => null,

            'tipo_item_custo' => 'SEM_CUSTO',
            'origem_custo' => 'SEM_CUSTO',

            'embalagem' => null,
            'custo_percentual' => 1,

            'unid_med' => null,
            'tipo_mp' => null,

            'custo_mp_unit' => 0,
            'custo_op_unit' => 0,
            'custo_total_unit' => 0,

            'encontrado' => false,
            'message' => $mensagem,
        ];
    }

    public function buscarProdutosPorNome(string $termo)
    {
        $termo = trim($termo);

        if ($termo === '') {
            return collect();
        }

        return DB::table('produto_custo_estrutura_mysql')
            ->select('cod', 'descr')
            ->where('descr', 'like', '%' . $termo . '%')
            ->groupBy('cod', 'descr')
            ->orderBy('descr')
            ->get();
    }

    private function normalizarCodigo(string $codigo): string
    {
        return trim((string) $codigo);
    }


    private function buscarEstruturaProduto(string $codigo, int $ano, int $mes): array
    {
        $itens = DB::table('produto_ficha_custo_mysql')
            ->where('cod', $codigo)
            ->where('ano', $ano)
            ->where('mes', $mes)
            ->select([
                'id',
                'cod_pai',
                'cod_comp',
                'desc_comp',
                'qtde_calc',
                'nivel',
                'tipo_custo',
                'tipo_mp',
                'custo_var',
                'base_op_qtd',
                'custo_mp_unit',
                'valor_mp',
                'custo_op_unit',
                'valor_op',
            ])
            ->orderBy('nivel')
            ->orderBy('tipo_custo')
            ->orderBy('desc_comp')
            ->get();

        // Itens abaixo de um componente já precificado não entram nos totais (evita dupla contagem).
        $considerados = config('ficha.regra_nova')
            ? FichaCustoHierarquia::considerados($itens->all())
            : array_fill_keys(array_keys($itens->all()), true);

        $itens = $itens->map(function ($item, $i) use ($considerados) {
            $item->considerado = $considerados[$i];

            return $item;
        });

        $materiasPrimas = $itens
            ->filter(function ($item) {
                return trim((string) $item->tipo_custo) === 'MATÉRIA-PRIMA';
            })
            ->values();

        $operacionais = $itens
            ->filter(function ($item) {
                return trim((string) $item->tipo_custo) === 'OPERACIONAL';
            })
            ->values();

        return [
            'encontrado' => $itens->isNotEmpty(),
            'ano' => $ano,
            'mes' => $mes,
            'ano_mes' => sprintf('%04d-%02d', $ano, $mes),

            'materias_primas' => $materiasPrimas,
            'operacionais' => $operacionais,

            'total_materia_prima' => (float) $materiasPrimas->where('considerado', true)->sum('valor_mp'),
            'total_operacional' => (float) $operacionais->where('considerado', true)->sum('valor_op'),
        ];
    }

    /**
     * Fator para converter o custo da peça em custo por unidade de venda (M2/M3) e a unidade.
     * Usa o gravado na consolidação; se não houver, deduz pelas dimensões da chapa na ficha
     * da própria competência e pela unidade do ERP.
     *
     * @return array{0: float, 1: ?string}
     */
    private function fatorUnidadeVenda(string $codigo, $estrutura): array
    {
        $fator = (float) ($estrutura->fator_unid ?? 1);
        $unidade = $estrutura->unid_med ?? null;

        if ($unidade === null || FichaCustoHierarquia::semFator($fator)) {
            try {
                $unidade = $unidade ?: trim((string) DB::connection(env('ERP_CONNECTION'))
                    ->table('PRODUTOS')->where('PRD_CODIGO', $codigo)->value('UM_CODIGO'));
            } catch (\Throwable $e) {
                $unidade = null;
            }
        }

        $descricoes = DB::table('produto_ficha_custo_mysql')
            ->where('cod', $codigo)
            ->where('ano', $estrutura->ano)
            ->where('mes', $estrutura->mes)
            ->pluck('desc_comp')
            ->all();

        $dim = FichaCustoHierarquia::dimensoesChapa((string) $estrutura->descr, $descricoes);

        if ($unidade === null || FichaCustoHierarquia::semFator($fator)) {
            $fator = max(FichaCustoHierarquia::fatorUnidade($unidade, $dim['base_m2'], $dim['base_m3']), 0.000001);
        }

        return [$fator, $unidade ?: null, $dim];
    }

    /**
     * Histórico de custo do produto por competência (da mais recente para a mais antiga),
     * com variação percentual em relação ao mês anterior.
     */
    public function historicoCustos(string $codigo, int $limite = 12): array
    {
        $codigo = $this->normalizarCodigo($codigo);

        $linhas = DB::table('produto_custo_estrutura_mysql')
            ->where('cod', $codigo)
            ->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->limit($limite + 1)
            ->get();

        if ($linhas->isEmpty()) {
            return ['encontrado' => false, 'itens' => [], 'unid_med' => null, 'fator_unid' => 1];
        }

        // Mesma chapa para todas as competências (usa a mais recente).
        [$fator, $unidade] = $this->fatorUnidadeVenda($codigo, $linhas->first());

        $itens = [];

        foreach ($linhas->values() as $i => $linha) {
            $anterior = $linhas->get($i + 1);
            $total = (float) $linha->custo_total;

            $itens[] = [
                'ano_mes' => trim((string) $linha->ano_mes),
                'custo_mp' => (float) $linha->custo_mp,
                'custo_op' => (float) $linha->custo_op,
                'custo_total' => $total,
                'custo_total_m2' => $total / $fator,
                'variacao' => ($anterior && (float) $anterior->custo_total > 0)
                    ? ($total / (float) $anterior->custo_total - 1) * 100
                    : null,
            ];
        }

        return [
            'encontrado' => true,
            'unid_med' => $unidade,
            'fator_unid' => $fator,
            'itens' => array_slice($itens, 0, $limite),
        ];
    }

    /**
     * Texto curto de onde veio o custo exibido.
     */
    private function descreverOrigem($estrutura): string
    {
        $base = DB::table('produto_custos_op_mysql')
            ->where('ano_mes', $estrutura->ano_mes)
            ->value('ano_mes_base');

        $fmt = function ($anoMes) {
            $anoMes = trim((string) $anoMes);

            return strlen($anoMes) === 7 ? substr($anoMes, 5, 2) . '/' . substr($anoMes, 0, 4) : $anoMes;
        };

        $texto = 'Estrutura do produto (ficha de custo) · competência ' . $fmt($estrutura->ano_mes);

        if ($base) {
            $texto .= ' · custos operacionais por hora com base em ' . $fmt($base);
        }

        return $texto;
    }

    /**
     * Alertas sobre a qualidade do custo exibido (dados suspeitos).
     *
     * @return array<int, array{nivel: string, mensagem: string}>
     */
    private function montarAlertas(string $codigo, $estrutura, array $itens, float $fator, ?string $unidade): array
    {
        $alertas = [];
        $total = (float) $estrutura->custo_total;
        $fmt = function ($anoMes) {
            $anoMes = trim((string) $anoMes);

            return strlen($anoMes) === 7 ? substr($anoMes, 5, 2) . '/' . substr($anoMes, 0, 4) : $anoMes;
        };

        // 1) Operacional praticamente zerado
        if ($total > 0 && (float) $estrutura->custo_op / $total < 0.02) {
            $alertas[] = [
                'nivel' => 'warning',
                'mensagem' => 'Custo operacional praticamente zerado ('
                    . number_format(((float) $estrutura->custo_op / $total) * 100, 2, ',', '.')
                    . '% do custo). Confira se as bases de m²/m³ dos processos foram calculadas.',
            ];
        }

        // 2) Matéria-prima (item final da estrutura) sem custo
        $mps = $itens['materias_primas'] ?? collect();
        $pais = collect($mps)->pluck('cod_pai')
            ->merge(collect($itens['operacionais'] ?? [])->pluck('cod_pai'))
            ->map(function ($c) {
                return trim((string) $c);
            })
            ->unique()
            ->flip();

        $semCusto = collect($mps)
            ->filter(function ($i) use ($pais) {
                return (float) $i->custo_mp_unit == 0 && !$pais->has(trim((string) $i->cod_comp));
            })
            ->pluck('desc_comp')
            ->unique()
            ->values();

        if ($semCusto->isNotEmpty()) {
            $alertas[] = [
                'nivel' => 'danger',
                'mensagem' => $semCusto->count() . ' matéria(s)-prima(s) sem custo: '
                    . $semCusto->take(3)->implode(', ') . ($semCusto->count() > 3 ? '…' : '') . '.',
            ];
        }

        // 3) Variação forte em relação ao mês anterior
        $anterior = DB::table('produto_custo_estrutura_mysql')
            ->where('cod', $codigo)
            ->where(function ($q) use ($estrutura) {
                $q->where('ano', '<', $estrutura->ano)
                    ->orWhere(function ($q2) use ($estrutura) {
                        $q2->where('ano', $estrutura->ano)->where('mes', '<', $estrutura->mes);
                    });
            })
            ->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->first();

        if ($anterior && (float) $anterior->custo_total > 0) {
            $variacao = ($total / (float) $anterior->custo_total - 1) * 100;

            if (abs($variacao) >= 15) {
                $alertas[] = [
                    'nivel' => 'warning',
                    'mensagem' => 'Custo variou ' . ($variacao > 0 ? '+' : '') . number_format($variacao, 1, ',', '.')
                        . '% em relação a ' . $fmt($anterior->ano_mes) . '.',
                ];
            }
        }

        // 4) Competência diferente da mais recente
        $maisRecente = DB::table('produto_custo_estrutura_mysql')
            ->where('cod', $codigo)
            ->orderBy('ano', 'desc')
            ->orderBy('mes', 'desc')
            ->value('ano_mes');

        if ($maisRecente && trim((string) $maisRecente) !== trim((string) $estrutura->ano_mes)) {
            $alertas[] = [
                'nivel' => 'info',
                'mensagem' => 'Você está vendo a competência ' . $fmt($estrutura->ano_mes)
                    . '; a mais recente é ' . $fmt($maisRecente) . '.',
            ];
        }

        // 5) Produto vendido em m²/m³ sem dimensões da chapa
        if (in_array(strtoupper((string) $unidade), ['M2', 'M3'], true) && FichaCustoHierarquia::semFator($fator)) {
            $alertas[] = [
                'nivel' => 'warning',
                'mensagem' => 'Produto vendido em ' . strtoupper((string) $unidade)
                    . ', mas não foi possível identificar as dimensões da chapa: o custo por ' . strtolower((string) $unidade)
                    . ' não pôde ser calculado.',
            ];
        }

        return $alertas;
    }
}
