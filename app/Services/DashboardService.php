<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    protected $connection;

    public function __construct($connection = null)
    {
        $this->connection = $connection ?: env('APP_NAME');
    }

    public function obterDashboard(array $filtros = [])
    {
        $filtros = $this->normalizarFiltros($filtros);
        $periodoAnterior = $this->calcularPeriodoAnterior($filtros['data_inicial'], $filtros['data_final']);

        $financeiro = $this->obterCardsFinanceiros($filtros);
        $previsaoCaixa = $this->obterPrevisaoCaixa($filtros);
        $inadimplencia = $this->obterInadimplencia($filtros);
        $comercial = $this->obterCardsComerciais($filtros);
        $comparativoVendas = $this->obterComparativoVendas($filtros);
        $evolucaoVendas = $this->obterEvolucaoVendas($filtros);
        $vendasRepresentante = $this->obterVendasPorRepresentante($filtros);
        $tiposPagamento = $this->obterReceberPorTipoPagamento($filtros);
        $situacoesPedidos = $this->obterSituacoesPedidos($filtros);
        $titulosVencidos = $this->obterMaioresTitulosVencidos($filtros);
        $clientes = $this->obterMaioresClientes($filtros);
        $produtos = $this->obterTopProdutos($filtros);

        return [
            'filtros' => $filtros,
            'periodo' => [
                'data_inicial' => $filtros['data_inicial'],
                'data_final' => $filtros['data_final'],
                'vencimento_inicial' => $filtros['vencimento_inicial'],
                'vencimento_final' => $filtros['vencimento_final'],
                'dias' => Carbon::parse($filtros['data_inicial'])->diffInDays(Carbon::parse($filtros['data_final'])) + 1,
            ],
            'periodo_anterior' => $periodoAnterior,
            'financeiro' => $financeiro,
            'previsao_caixa' => $previsaoCaixa,
            'inadimplencia' => $inadimplencia,
            'comercial' => $comercial,
            'comparativo_vendas' => $comparativoVendas,
            'evolucao_vendas' => $evolucaoVendas,
            'vendas_representante' => $vendasRepresentante,
            'tipos_pagamento' => $tiposPagamento,
            'situacoes_pedidos' => $situacoesPedidos,
            'titulos_vencidos' => $titulosVencidos,
            'clientes' => $clientes,
            'produtos' => $produtos,
            'memoria_calculo' => $this->montarMemoriaCalculo(
                $filtros,
                $financeiro,
                $previsaoCaixa,
                $inadimplencia,
                $comercial,
                $comparativoVendas,
                $evolucaoVendas,
                $vendasRepresentante,
                $tiposPagamento,
                $situacoesPedidos,
                $titulosVencidos,
                $clientes,
                $produtos
            ),
        ];
    }

    public function obterCardsFinanceiros(array $filtros)
    {
        $duplicatas = $this->obterResumoDuplicatas($filtros);
        $cheques = $this->obterResumoCheques($filtros);
        $pagar = $this->obterResumoPagar($filtros);

        $totalDuplicatasSemEncargos = (float) ($duplicatas->VALOR_ORIGINAL ?? 0)
            - (float) ($duplicatas->VALOR_PAGO ?? 0)
            - (float) ($duplicatas->CREDITOS ?? 0)
            - (float) ($duplicatas->VALOR_DESCONTO ?? 0)
            + (float) ($duplicatas->VALOR_OUTRAS_DESPESAS ?? 0);
        $totalChequesSemEncargos = (float) ($cheques->TOTAL ?? 0);
        $totalReceberSemEncargos = $totalDuplicatasSemEncargos + $totalChequesSemEncargos;

        $totalReceber = (float) ($duplicatas->TOTAL ?? 0) + (float) ($cheques->TOTAL ?? 0);
        $totalReceberVencido = (float) ($duplicatas->VENCIDO ?? 0) + (float) ($cheques->VENCIDO ?? 0);
        $totalReceberAVencer = (float) ($duplicatas->A_VENCER ?? 0) + (float) ($cheques->A_VENCER ?? 0);

        return (object) [
            'TOTAL_RECEBER' => $totalReceber,
            'TOTAL_RECEBER_SEM_ENCARGOS' => $totalReceberSemEncargos,
            'TOTAL_RECEBER_DUPLICATAS_SEM_ENCARGOS' => $totalDuplicatasSemEncargos,
            'TOTAL_RECEBER_CHEQUES_SEM_ENCARGOS' => $totalChequesSemEncargos,
            'TOTAL_RECEBER_DUPLICATAS' => (float) ($duplicatas->TOTAL ?? 0),
            'TOTAL_RECEBER_DUPLICATAS_BRUTO' => (float) ($duplicatas->DUPLICATAS ?? 0),
            'TOTAL_RECEBER_CREDITOS' => (float) ($duplicatas->CREDITOS ?? 0),
            'TOTAL_RECEBER_CREDITOS_NEGATIVO' => -(float) ($duplicatas->CREDITOS ?? 0),
            'TOTAL_RECEBER_CHEQUES' => (float) ($cheques->TOTAL ?? 0),
            'RECEBER_VALOR_ORIGINAL' => (float) ($duplicatas->VALOR_ORIGINAL ?? 0) + (float) ($cheques->TOTAL ?? 0),
            'RECEBER_VALOR_PAGO' => (float) ($duplicatas->VALOR_PAGO ?? 0),
            'RECEBER_VALOR_JUROS' => (float) ($duplicatas->VALOR_JUROS ?? 0),
            'RECEBER_VALOR_MULTA' => (float) ($duplicatas->VALOR_MULTA ?? 0),
            'RECEBER_JUROS_MULTA' => (float) ($duplicatas->VALOR_JUROS ?? 0) + (float) ($duplicatas->VALOR_MULTA ?? 0),
            'RECEBER_VALOR_DESCONTO' => (float) ($duplicatas->VALOR_DESCONTO ?? 0),
            'RECEBER_OUTRAS_DESPESAS' => (float) ($duplicatas->VALOR_OUTRAS_DESPESAS ?? 0),
            'RECEBER_SALDO_ATUALIZADO' => $totalReceber,
            'RECEBER_SALDO_ORIGINAL_MENOS_PAGAMENTOS' => (float) ($duplicatas->VALOR_ORIGINAL ?? 0) - (float) ($duplicatas->VALOR_PAGO ?? 0),
            'RECEBER_ORIGINAL_VENCIDO' => (float) ($duplicatas->ORIGINAL_VENCIDO ?? 0),
            'RECEBER_PAGO_VENCIDO' => (float) ($duplicatas->PAGO_VENCIDO ?? 0),
            'RECEBER_CREDITOS_VENCIDOS' => (float) ($duplicatas->CREDITOS_VENCIDOS ?? 0),
            'RECEBER_DESCONTO_VENCIDO' => (float) ($duplicatas->DESCONTO_VENCIDO ?? 0),
            'RECEBER_OUTRAS_DESPESAS_VENCIDAS' => (float) ($duplicatas->OUTRAS_DESPESAS_VENCIDAS ?? 0),
            'RECEBER_JUROS_VENCIDOS' => (float) ($duplicatas->JUROS_VENCIDOS ?? 0),
            'RECEBER_MULTA_VENCIDA' => (float) ($duplicatas->MULTA_VENCIDA ?? 0),
            'SALDO_PROJETADO_ORIGINAL' => $totalReceberSemEncargos - (float) ($pagar->TOTAL ?? 0),
            'TOTAL_PAGAR' => (float) ($pagar->TOTAL ?? 0),
            'SALDO_PROJETADO' => $totalReceber - (float) ($pagar->TOTAL ?? 0),
            'TOTAL_RECEBER_VENCIDO' => $totalReceberVencido,
            'TOTAL_RECEBER_VENCIDO_DUPLICATAS' => (float) ($duplicatas->VENCIDO ?? 0),
            'TOTAL_RECEBER_VENCIDO_CHEQUES' => (float) ($cheques->VENCIDO ?? 0),
            'TOTAL_RECEBER_A_VENCER' => $totalReceberAVencer,
            'TOTAL_RECEBER_A_VENCER_DUPLICATAS' => (float) ($duplicatas->A_VENCER ?? 0),
            'TOTAL_RECEBER_A_VENCER_CHEQUES' => (float) ($cheques->A_VENCER ?? 0),
            'TOTAL_PAGAR_VENCIDO' => (float) ($pagar->VENCIDO ?? 0),
            'TOTAL_PAGAR_A_VENCER' => (float) ($pagar->A_VENCER ?? 0),
            'QTD_TITULOS_RECEBER' => (int) ($duplicatas->QUANTIDADE ?? 0) + (int) ($cheques->QUANTIDADE ?? 0),
            'QTD_TITULOS_RECEBER_DUPLICATAS' => (int) ($duplicatas->QUANTIDADE ?? 0),
            'QTD_TITULOS_RECEBER_CREDITOS' => (int) ($duplicatas->QTD_CREDITOS ?? 0),
            'QTD_TITULOS_RECEBER_CHEQUES' => (int) ($cheques->QUANTIDADE ?? 0),
            'QTD_TITULOS_PAGAR' => (int) ($pagar->QUANTIDADE ?? 0),
        ];
    }

    public function obterPrevisaoCaixa(array $filtros)
    {
        $duplicatas = $this->obterPrevisaoDuplicatas($filtros);
        $cheques = $this->obterPrevisaoCheques($filtros);
        $pagar = $this->obterPrevisaoPagar($filtros);

        return $this->consolidarPrevisaoCaixa($duplicatas, $cheques, $pagar);
    }

    public function obterInadimplencia(array $filtros)
    {
        $duplicatas = $this->obterInadimplenciaDuplicatas($filtros);
        $cheques = $this->obterInadimplenciaCheques($filtros);

        $vencidoDuplicatas = (float) ($duplicatas->VENCIDO ?? 0);
        $vencidoCheques = (float) ($cheques->VENCIDO ?? 0);
        $totalDuplicatas = (float) ($duplicatas->TOTAL ?? 0);
        $totalCheques = (float) ($cheques->TOTAL ?? 0);
        $vencido = $vencidoDuplicatas + $vencidoCheques;
        $total = $totalDuplicatas + $totalCheques;

        return (object) [
            'SALDO_VENCIDO' => $vencido,
            'SALDO_TOTAL_PERIODO' => $total,
            'SALDO_VENCIDO_DUPLICATAS' => $vencidoDuplicatas,
            'SALDO_VENCIDO_CHEQUES' => $vencidoCheques,
            'SALDO_TOTAL_DUPLICATAS' => $totalDuplicatas,
            'SALDO_TOTAL_CHEQUES' => $totalCheques,
            'PERCENTUAL_INADIMPLENCIA' => $total > 0 ? ($vencido / $total) * 100 : 0,
            'QTD_TITULOS_VENCIDOS' => (int) ($duplicatas->QTD_VENCIDOS ?? 0) + (int) ($cheques->QTD_VENCIDOS ?? 0),
            'QTD_TITULOS_PERIODO' => (int) ($duplicatas->QUANTIDADE ?? 0) + (int) ($cheques->QUANTIDADE ?? 0),
        ];
    }

    public function obterReceberPorTipoPagamento(array $filtros)
    {
        list($filtroDuplicata, $bindingsDuplicata) = $this->montarFiltroRecebivelDuplicata($filtros);
        list($filtroCheque, $bindingsCheque) = $this->montarFiltroRecebivelCheque($filtros);

        $duplicatas = collect($this->selecionar("
            SELECT CASE WHEN FC.CON_TIPO = 2 THEN 'CRÉDITO' ELSE COALESCE(NULLIF(TRIM(TPG.CON_TIPO_DESC), ''), 'NÃO INFORMADO') END AS TIPO_PAGAMENTO,
                   COUNT(*) AS QTD_TITULOS,
                   COALESCE(SUM(CASE WHEN FC.CON_TIPO = 2 THEN -FC.CON_VALOR_CORRIGIDO ELSE FC.CON_VALOR_CORRIGIDO END), 0) AS SALDO_RECEBER
            FROM FIN_CONTAS FC
            LEFT JOIN FIN_CONTAS_TIPOS TPG ON TPG.CON_TIPO_PAGAMENTO = FC.CON_TIPO_PAGAMENTO
            WHERE FC.CON_TIPO IN (0, 2)
              AND FC.CON_PREVISAO = 0
              AND FC.CON_SITUACAO IN (0, 1)
              AND COALESCE(FC.CON_VALOR_CORRIGIDO, 0) > 0
              {$filtroDuplicata}
            GROUP BY CASE WHEN FC.CON_TIPO = 2 THEN 'CRÉDITO' ELSE COALESCE(NULLIF(TRIM(TPG.CON_TIPO_DESC), ''), 'NÃO INFORMADO') END
        ", $bindingsDuplicata));

        $cheque = $this->primeiro("
            SELECT 'CHEQUE' AS TIPO_PAGAMENTO,
                   COUNT(*) AS QTD_TITULOS,
                   COALESCE(SUM(CHQ.CHQ_VALOR), 0) AS SALDO_RECEBER
            FROM CHEQUE CHQ
            WHERE CHQ.CHQ_TERC = 0
              AND CHQ.CHQ_SITUACAO = 0
              AND COALESCE(CHQ.CHQ_VALOR, 0) > 0
              AND EXISTS (SELECT 1 FROM FIN_CONTAS_CHEQUES FCQ INNER JOIN FIN_CONTAS FC ON FC.CON_CODIGO = FCQ.CON_CODIGO WHERE FCQ.CHQ_CODIGO = CHQ.CHQ_CODIGO AND FC.CON_TIPO = 0)
              {$filtroCheque}
        ", $bindingsCheque);

        if ((int) ($cheque->QTD_TITULOS ?? 0) > 0) {
            $duplicatas->push($cheque);
        }

        $agrupados = $duplicatas->groupBy('TIPO_PAGAMENTO')->map(function ($itens, $tipo) {
            return (object) [
                'TIPO_PAGAMENTO' => $tipo,
                'QTD_TITULOS' => (int) $itens->sum('QTD_TITULOS'),
                'SALDO_RECEBER' => (float) $itens->sum('SALDO_RECEBER'),
            ];
        })->sortByDesc('SALDO_RECEBER')->values();

        $total = (float) $agrupados->sum('SALDO_RECEBER');
        return $agrupados->map(function ($item) use ($total) {
            $item->PERCENTUAL = $total > 0 ? ((float) $item->SALDO_RECEBER / $total) * 100 : 0;
            return $item;
        });
    }

    public function obterMaioresTitulosVencidos(array $filtros)
    {
        list($filtroDuplicata, $bindingsDuplicata) = $this->montarFiltroRecebivelDuplicata($filtros);
        list($filtroCheque, $bindingsCheque) = $this->montarFiltroRecebivelCheque($filtros);

        $duplicatas = collect($this->selecionar("
            SELECT FIRST 10 FC.CON_CODIGO, FC.CON_NUMERO, FC.CON_SEQUENCIA,
                   CAST(FC.CON_DT_VENCIMENTO AS DATE) AS CON_DT_VENCIMENTO,
                   CURRENT_DATE - CAST(FC.CON_DT_VENCIMENTO AS DATE) AS DIAS_ATRASO,
                   FC.CON_VALOR_ORIGINAL, FC.CON_VALOR_CORRIGIDO AS SALDO_ATUAL,
                   CLI.PART_CLIENTE_CODIGO AS COD_CLIENTE, CLI.PART_NOME AS CLIENTE,
                   'DUPLICATA' AS ORIGEM,
                   COALESCE(NULLIF(TRIM(TPG.CON_TIPO_DESC), ''), 'NÃO INFORMADO') AS TIPO_PAGAMENTO
            FROM FIN_CONTAS FC
            LEFT JOIN PARTICIPANTE CLI ON CLI.PART_CODIGO = FC.PART_CODIGO
            LEFT JOIN FIN_CONTAS_TIPOS TPG ON TPG.CON_TIPO_PAGAMENTO = FC.CON_TIPO_PAGAMENTO
            WHERE FC.CON_TIPO = 0 AND FC.CON_PREVISAO = 0 AND FC.CON_SITUACAO IN (0, 1)
              AND COALESCE(FC.CON_VALOR_CORRIGIDO, 0) > 0
              AND CAST(FC.CON_DT_VENCIMENTO AS DATE) <= CURRENT_DATE
              {$filtroDuplicata}
            ORDER BY FC.CON_VALOR_CORRIGIDO DESC
        ", $bindingsDuplicata));

        $cheques = collect($this->selecionar("
            SELECT FIRST 10 CHQ.CHQ_CODIGO AS CON_CODIGO, 'Nr.' || CHQ.CHQ_CHEQUE AS CON_NUMERO,
                   0 AS CON_SEQUENCIA, CAST(CHQ.CHQ_BOM_PARA AS DATE) AS CON_DT_VENCIMENTO,
                   CURRENT_DATE - CAST(CHQ.CHQ_BOM_PARA AS DATE) AS DIAS_ATRASO,
                   CHQ.CHQ_VALOR AS CON_VALOR_ORIGINAL, CHQ.CHQ_VALOR AS SALDO_ATUAL,
                   MAX(CLI.PART_CLIENTE_CODIGO) AS COD_CLIENTE, MAX(CLI.PART_NOME) AS CLIENTE,
                   'CHEQUE' AS ORIGEM, 'CHEQUE' AS TIPO_PAGAMENTO
            FROM CHEQUE CHQ
            INNER JOIN FIN_CONTAS_CHEQUES FCQ ON FCQ.CHQ_CODIGO = CHQ.CHQ_CODIGO
            INNER JOIN FIN_CONTAS FC ON FC.CON_CODIGO = FCQ.CON_CODIGO
            LEFT JOIN PARTICIPANTE CLI ON CLI.PART_CODIGO = FC.PART_CODIGO
            WHERE CHQ.CHQ_TERC = 0 AND CHQ.CHQ_SITUACAO = 0 AND FC.CON_TIPO = 0
              AND COALESCE(CHQ.CHQ_VALOR, 0) > 0
              AND CAST(CHQ.CHQ_BOM_PARA AS DATE) <= CURRENT_DATE
              {$filtroCheque}
            GROUP BY CHQ.CHQ_CODIGO, CHQ.CHQ_CHEQUE, CHQ.CHQ_BOM_PARA, CHQ.CHQ_VALOR
            ORDER BY CHQ.CHQ_VALOR DESC
        ", $bindingsCheque));

        return $duplicatas->concat($cheques)->sortByDesc('SALDO_ATUAL')->take(10)->values();
    }

    protected function obterResumoDuplicatas(array $filtros)
    {
        list($where, $bindings) = $this->montarFiltroRecebivelDuplicata($filtros);

        $valorComSinal = "CASE WHEN FC.CON_TIPO = 2 THEN -FC.CON_VALOR_CORRIGIDO ELSE FC.CON_VALOR_CORRIGIDO END";
        $originalComSinal = "CASE WHEN FC.CON_TIPO = 2 THEN -FC.CON_VALOR_ORIGINAL ELSE FC.CON_VALOR_ORIGINAL END";

        return $this->primeiro("
            SELECT
                COALESCE(SUM({$originalComSinal}), 0) AS VALOR_ORIGINAL,
                COALESCE(SUM(CASE WHEN FC.CON_TIPO = 0 THEN FC.CON_VALOR_TOTAL_PAGO ELSE 0 END), 0) AS VALOR_PAGO,
                COALESCE(SUM(CASE WHEN FC.CON_TIPO = 0 THEN FC.CON_VALOR_JUROS ELSE 0 END), 0) AS VALOR_JUROS,
                COALESCE(SUM(CASE WHEN FC.CON_TIPO = 0 THEN FC.CON_VALOR_MULTA ELSE 0 END), 0) AS VALOR_MULTA,
                COALESCE(SUM(CASE WHEN FC.CON_TIPO = 0 THEN FC.CON_VALOR_DESCONTO ELSE 0 END), 0) AS VALOR_DESCONTO,
                COALESCE(SUM(CASE WHEN FC.CON_TIPO = 0 THEN FC.CON_VALOR_OUTRASDESPESAS ELSE 0 END), 0) AS VALOR_OUTRAS_DESPESAS,
                COALESCE(SUM({$valorComSinal}), 0) AS TOTAL,
                COALESCE(SUM(CASE WHEN FC.CON_TIPO = 0 THEN FC.CON_VALOR_CORRIGIDO ELSE 0 END), 0) AS DUPLICATAS,
                COALESCE(SUM(CASE WHEN FC.CON_TIPO = 2 THEN FC.CON_VALOR_CORRIGIDO ELSE 0 END), 0) AS CREDITOS,
                COALESCE(SUM(CASE WHEN CAST(FC.CON_DT_VENCIMENTO AS DATE) <= CURRENT_DATE AND FC.CON_TIPO = 0 THEN FC.CON_VALOR_ORIGINAL ELSE 0 END), 0) AS ORIGINAL_VENCIDO,
                COALESCE(SUM(CASE WHEN CAST(FC.CON_DT_VENCIMENTO AS DATE) <= CURRENT_DATE AND FC.CON_TIPO = 0 THEN FC.CON_VALOR_TOTAL_PAGO ELSE 0 END), 0) AS PAGO_VENCIDO,
                COALESCE(SUM(CASE WHEN CAST(FC.CON_DT_VENCIMENTO AS DATE) <= CURRENT_DATE AND FC.CON_TIPO = 2 THEN FC.CON_VALOR_CORRIGIDO ELSE 0 END), 0) AS CREDITOS_VENCIDOS,
                COALESCE(SUM(CASE WHEN CAST(FC.CON_DT_VENCIMENTO AS DATE) <= CURRENT_DATE AND FC.CON_TIPO = 0 THEN FC.CON_VALOR_DESCONTO ELSE 0 END), 0) AS DESCONTO_VENCIDO,
                COALESCE(SUM(CASE WHEN CAST(FC.CON_DT_VENCIMENTO AS DATE) <= CURRENT_DATE AND FC.CON_TIPO = 0 THEN FC.CON_VALOR_OUTRASDESPESAS ELSE 0 END), 0) AS OUTRAS_DESPESAS_VENCIDAS,
                COALESCE(SUM(CASE WHEN CAST(FC.CON_DT_VENCIMENTO AS DATE) <= CURRENT_DATE AND FC.CON_TIPO = 0 THEN FC.CON_VALOR_JUROS ELSE 0 END), 0) AS JUROS_VENCIDOS,
                COALESCE(SUM(CASE WHEN CAST(FC.CON_DT_VENCIMENTO AS DATE) <= CURRENT_DATE AND FC.CON_TIPO = 0 THEN FC.CON_VALOR_MULTA ELSE 0 END), 0) AS MULTA_VENCIDA,
                COALESCE(SUM(CASE WHEN CAST(FC.CON_DT_VENCIMENTO AS DATE) <= CURRENT_DATE THEN {$valorComSinal} ELSE 0 END), 0) AS VENCIDO,
                COALESCE(SUM(CASE WHEN CAST(FC.CON_DT_VENCIMENTO AS DATE) > CURRENT_DATE THEN {$valorComSinal} ELSE 0 END), 0) AS A_VENCER,
                COALESCE(SUM(CASE WHEN FC.CON_TIPO = 0 THEN 1 ELSE 0 END), 0) AS QTD_DUPLICATAS,
                COALESCE(SUM(CASE WHEN FC.CON_TIPO = 2 THEN 1 ELSE 0 END), 0) AS QTD_CREDITOS,
                COUNT(*) AS QUANTIDADE
            FROM FIN_CONTAS FC
            WHERE FC.CON_TIPO IN (0, 2)
              AND FC.CON_PREVISAO = 0
              AND FC.CON_SITUACAO IN (0, 1)
              AND COALESCE(FC.CON_VALOR_CORRIGIDO, 0) > 0
              {$where}
        ", $bindings);
    }

    protected function obterResumoCheques(array $filtros)
    {
        list($where, $bindings) = $this->montarFiltroRecebivelCheque($filtros);
        return $this->primeiro("SELECT COALESCE(SUM(CHQ.CHQ_VALOR),0) AS TOTAL, COALESCE(SUM(CASE WHEN CAST(CHQ.CHQ_BOM_PARA AS DATE)<=CURRENT_DATE THEN CHQ.CHQ_VALOR ELSE 0 END),0) AS VENCIDO, COALESCE(SUM(CASE WHEN CAST(CHQ.CHQ_BOM_PARA AS DATE)>CURRENT_DATE THEN CHQ.CHQ_VALOR ELSE 0 END),0) AS A_VENCER, COUNT(*) AS QUANTIDADE FROM CHEQUE CHQ WHERE CHQ.CHQ_TERC=0 AND CHQ.CHQ_SITUACAO=0 AND COALESCE(CHQ.CHQ_VALOR,0)>0 AND EXISTS (SELECT 1 FROM FIN_CONTAS_CHEQUES FCQ INNER JOIN FIN_CONTAS FC ON FC.CON_CODIGO=FCQ.CON_CODIGO WHERE FCQ.CHQ_CODIGO=CHQ.CHQ_CODIGO AND FC.CON_TIPO=0) {$where}", $bindings);
    }

    protected function obterResumoPagar(array $filtros)
    {
        list($where, $bindings) = $this->montarFiltroContasPagar($filtros, 'FP');
        return $this->primeiro("SELECT COALESCE(SUM(FP.CON_VALOR_CORRIGIDO),0) AS TOTAL, COALESCE(SUM(CASE WHEN CAST(FP.CON_DT_VENCIMENTO AS DATE)<=CURRENT_DATE THEN FP.CON_VALOR_CORRIGIDO ELSE 0 END),0) AS VENCIDO, COALESCE(SUM(CASE WHEN CAST(FP.CON_DT_VENCIMENTO AS DATE)>CURRENT_DATE THEN FP.CON_VALOR_CORRIGIDO ELSE 0 END),0) AS A_VENCER, COUNT(*) AS QUANTIDADE FROM FIN_CONTAS FP WHERE FP.CON_TIPO=1 AND FP.CON_PREVISAO=0 AND FP.CON_SITUACAO IN(0,1) AND COALESCE(FP.CON_VALOR_CORRIGIDO,0)>0 {$where}", $bindings);
    }

    protected function obterPrevisaoDuplicatas(array $filtros)
    {
        list($where, $bindings) = $this->montarFiltroRecebivelDuplicata($filtros);

        $campoValor = "CASE WHEN FC.CON_TIPO = 2 THEN -FC.CON_VALOR_CORRIGIDO ELSE FC.CON_VALOR_CORRIGIDO END";
        $fromWhere = "FIN_CONTAS FC WHERE FC.CON_TIPO IN (0,2) AND FC.CON_PREVISAO=0 AND FC.CON_SITUACAO IN(0,1) AND COALESCE(FC.CON_VALOR_CORRIGIDO,0)>0 {$where}";

        return $this->primeiro(
            $this->sqlPrevisaoAgregada('FC.CON_DT_VENCIMENTO', $campoValor, $fromWhere),
            $bindings
        );
    }

    protected function obterPrevisaoCheques(array $filtros)
    {
        list($where, $bindings) = $this->montarFiltroRecebivelCheque($filtros);
        return $this->primeiro($this->sqlPrevisaoAgregada('CHQ.CHQ_BOM_PARA', 'CHQ.CHQ_VALOR', "CHEQUE CHQ WHERE CHQ.CHQ_TERC=0 AND CHQ.CHQ_SITUACAO=0 AND COALESCE(CHQ.CHQ_VALOR,0)>0 AND EXISTS (SELECT 1 FROM FIN_CONTAS_CHEQUES FCQ INNER JOIN FIN_CONTAS FC ON FC.CON_CODIGO=FCQ.CON_CODIGO WHERE FCQ.CHQ_CODIGO=CHQ.CHQ_CODIGO AND FC.CON_TIPO=0) {$where}"), $bindings);
    }

    protected function obterPrevisaoPagar(array $filtros)
    {
        list($where, $bindings) = $this->montarFiltroContasPagar($filtros, 'FP');
        return $this->primeiro($this->sqlPrevisaoAgregada('FP.CON_DT_VENCIMENTO', 'FP.CON_VALOR_CORRIGIDO', "FIN_CONTAS FP WHERE FP.CON_TIPO=1 AND FP.CON_PREVISAO=0 AND FP.CON_SITUACAO IN(0,1) AND COALESCE(FP.CON_VALOR_CORRIGIDO,0)>0 {$where}"), $bindings);
    }

    protected function sqlPrevisaoAgregada($campoData, $campoValor, $fromWhere)
    {
        return "SELECT COALESCE(SUM(CASE WHEN CAST({$campoData} AS DATE)=CURRENT_DATE THEN {$campoValor} ELSE 0 END),0) AS HOJE, COALESCE(SUM(CASE WHEN CAST({$campoData} AS DATE)>CURRENT_DATE AND CAST({$campoData} AS DATE)<=CURRENT_DATE+7 THEN {$campoValor} ELSE 0 END),0) AS SETE_DIAS, COALESCE(SUM(CASE WHEN CAST({$campoData} AS DATE)>CURRENT_DATE AND CAST({$campoData} AS DATE)<=CURRENT_DATE+30 THEN {$campoValor} ELSE 0 END),0) AS TRINTA_DIAS FROM {$fromWhere}";
    }

    protected function consolidarPrevisaoCaixa($duplicatas, $cheques, $pagar)
    {
        $resultado = [];
        foreach (['HOJE' => 'HOJE', '7_DIAS' => 'SETE_DIAS', '30_DIAS' => 'TRINTA_DIAS'] as $saida => $campo) {
            $dup = (float)($duplicatas->{$campo} ?? 0);
            $chq = (float)($cheques->{$campo} ?? 0);
            $pag = (float)($pagar->{$campo} ?? 0);
            $resultado['RECEBER_' . $saida . '_DUPLICATAS'] = $dup;
            $resultado['RECEBER_' . $saida . '_CHEQUES'] = $chq;
            $resultado['RECEBER_' . $saida] = $dup + $chq;
            $resultado['PAGAR_' . $saida] = $pag;
            $resultado['SALDO_' . $saida] = $dup + $chq - $pag;
        }
        return (object) $resultado;
    }

    protected function obterInadimplenciaDuplicatas(array $filtros)
    {
        list($where, $bindings) = $this->montarFiltroRecebivelDuplicata($filtros);
        return $this->primeiro("SELECT COALESCE(SUM(FC.CON_VALOR_ORIGINAL),0) TOTAL, COALESCE(SUM(CASE WHEN CAST(FC.CON_DT_VENCIMENTO AS DATE)<=CURRENT_DATE AND FC.CON_SITUACAO IN(0,1) AND COALESCE(FC.CON_VALOR_CORRIGIDO,0)>0 THEN FC.CON_VALOR_CORRIGIDO ELSE 0 END),0) VENCIDO, COUNT(*) QUANTIDADE, COALESCE(SUM(CASE WHEN CAST(FC.CON_DT_VENCIMENTO AS DATE)<=CURRENT_DATE AND FC.CON_SITUACAO IN(0,1) AND COALESCE(FC.CON_VALOR_CORRIGIDO,0)>0 THEN 1 ELSE 0 END),0) QTD_VENCIDOS FROM FIN_CONTAS FC WHERE FC.CON_TIPO=0 AND FC.CON_PREVISAO=0 {$where}", $bindings);
    }

    protected function obterInadimplenciaCheques(array $filtros)
    {
        list($where, $bindings) = $this->montarFiltroRecebivelCheque($filtros);
        return $this->primeiro("SELECT COALESCE(SUM(CHQ.CHQ_VALOR),0) TOTAL, COALESCE(SUM(CASE WHEN CAST(CHQ.CHQ_BOM_PARA AS DATE)<=CURRENT_DATE AND CHQ.CHQ_SITUACAO=0 AND COALESCE(CHQ.CHQ_VALOR,0)>0 THEN CHQ.CHQ_VALOR ELSE 0 END),0) VENCIDO, COUNT(*) QUANTIDADE, COALESCE(SUM(CASE WHEN CAST(CHQ.CHQ_BOM_PARA AS DATE)<=CURRENT_DATE AND CHQ.CHQ_SITUACAO=0 AND COALESCE(CHQ.CHQ_VALOR,0)>0 THEN 1 ELSE 0 END),0) QTD_VENCIDOS FROM CHEQUE CHQ WHERE CHQ.CHQ_TERC=0 AND EXISTS (SELECT 1 FROM FIN_CONTAS_CHEQUES FCQ INNER JOIN FIN_CONTAS FC ON FC.CON_CODIGO=FCQ.CON_CODIGO WHERE FCQ.CHQ_CODIGO=CHQ.CHQ_CODIGO AND FC.CON_TIPO=0 AND FC.CON_PREVISAO=0) {$where}", $bindings);
    }


    protected function montarFiltroRecebivelDuplicata(array $filtros)
    {
        $sql = " AND CAST(FC.CON_DT_VENCIMENTO AS DATE) BETWEEN ? AND ?";
        $bindings = [$filtros['vencimento_inicial'], $filtros['vencimento_final']];

        if (!empty($filtros['rep_codigo'])) {
            $sql .= " AND EXISTS (SELECT 1 FROM FIN_CONTAS_COMISSAO FCD WHERE FCD.CON_CODIGO = FC.CON_CODIGO AND FCD.PART_REPRESENTANTE_CODIGO = ?)";
            $bindings[] = (int) $filtros['rep_codigo'];
        }

        if (!empty($filtros['part_codigo'])) {
            $sql .= " AND FC.PART_CODIGO = ?";
            $bindings[] = (int) $filtros['part_codigo'];
        }

        return [$sql, $bindings];
    }

    protected function montarFiltroRecebivelCheque(array $filtros)
    {
        $sql = " AND CAST(CHQ.CHQ_BOM_PARA AS DATE) BETWEEN ? AND ?";
        $bindings = [$filtros['vencimento_inicial'], $filtros['vencimento_final']];

        if (!empty($filtros['rep_codigo'])) {
            $sql .= " AND EXISTS (SELECT 1 FROM FIN_CONTAS_CHEQUES FCR INNER JOIN FIN_CONTAS_COMISSAO FCM ON FCM.CON_CODIGO = FCR.CON_CODIGO WHERE FCR.CHQ_CODIGO = CHQ.CHQ_CODIGO AND FCM.PART_REPRESENTANTE_CODIGO = ?)";
            $bindings[] = (int) $filtros['rep_codigo'];
        }

        if (!empty($filtros['part_codigo'])) {
            $sql .= " AND EXISTS (SELECT 1 FROM FIN_CONTAS_CHEQUES FCC INNER JOIN FIN_CONTAS FCLI ON FCLI.CON_CODIGO = FCC.CON_CODIGO WHERE FCC.CHQ_CODIGO = CHQ.CHQ_CODIGO AND FCLI.CON_TIPO = 0 AND FCLI.PART_CODIGO = ?)";
            $bindings[] = (int) $filtros['part_codigo'];
        }

        return [$sql, $bindings];
    }

    protected function montarFiltroContasPagar(array $filtros, $alias = 'FP')
    {
        $sql = " AND CAST({$alias}.CON_DT_VENCIMENTO AS DATE) BETWEEN ? AND ?";
        $bindings = [$filtros['vencimento_inicial'], $filtros['vencimento_final']];

        if (!empty($filtros['rep_codigo'])) {
            $sql .= " AND EXISTS (SELECT 1 FROM FIN_CONTAS_COMISSAO FCP WHERE FCP.CON_CODIGO = {$alias}.CON_CODIGO AND FCP.PART_REPRESENTANTE_CODIGO = ?)";
            $bindings[] = (int) $filtros['rep_codigo'];
        }

        if (!empty($filtros['part_codigo'])) {
            $sql .= " AND {$alias}.PART_CODIGO = ?";
            $bindings[] = (int) $filtros['part_codigo'];
        }

        return [$sql, $bindings];
    }

    public function obterCardsComerciais(array $filtros)
    {
        list($where, $bindings) = $this->montarFiltroDav($filtros, 'D');
        $sql = "SELECT COALESCE(SUM(CASE WHEN D.DAV_SITUACAO IN (1,3) THEN D.DAV_VALOR_LIQUIDO ELSE 0 END),0) AS VENDAS_PERIODO, COALESCE(SUM(CASE WHEN D.DAV_SITUACAO IN (1,3) THEN 1 ELSE 0 END),0) AS QTD_PEDIDOS_VALIDOS, CASE WHEN SUM(CASE WHEN D.DAV_SITUACAO IN (1,3) THEN 1 ELSE 0 END)>0 THEN SUM(CASE WHEN D.DAV_SITUACAO IN (1,3) THEN D.DAV_VALOR_LIQUIDO ELSE 0 END)/SUM(CASE WHEN D.DAV_SITUACAO IN (1,3) THEN 1 ELSE 0 END) ELSE 0 END AS TICKET_MEDIO, COALESCE(SUM(CASE WHEN D.DAV_SITUACAO=0 THEN 1 ELSE 0 END),0) AS QTD_PEDIDOS_PENDENTES, COALESCE(SUM(CASE WHEN D.DAV_SITUACAO=0 THEN D.DAV_VALOR_LIQUIDO ELSE 0 END),0) AS VALOR_PEDIDOS_PENDENTES FROM DAV D WHERE D.DAV_TIPO='PD' AND D.DAV_SITUACAO IN(0,1,3) AND CAST(D.DAV_DATA_EMISSAO AS DATE) BETWEEN ? AND ? {$where}";
        $resultado = $this->primeiro($sql, array_merge([$filtros['data_inicial'], $filtros['data_final']], $bindings));
        $resultado->CLIENTES_ATENDIDOS = count($this->selecionar("SELECT D.PART_CODIGO FROM DAV D WHERE D.DAV_TIPO='PD' AND D.DAV_SITUACAO IN(1,3) AND D.PART_CODIGO IS NOT NULL AND CAST(D.DAV_DATA_EMISSAO AS DATE) BETWEEN ? AND ? {$where} GROUP BY D.PART_CODIGO", array_merge([$filtros['data_inicial'], $filtros['data_final']], $bindings)));
        return $resultado;
    }

    public function obterComparativoVendas(array $filtros)
    {
        $anterior = $this->calcularPeriodoAnterior($filtros['data_inicial'], $filtros['data_final']);
        list($where, $bindings) = $this->montarFiltroDav($filtros, 'D');
        return $this->primeiro("SELECT SUM(CASE WHEN CAST(D.DAV_DATA_EMISSAO AS DATE) BETWEEN ? AND ? THEN D.DAV_VALOR_LIQUIDO ELSE 0 END) AS VENDAS_ATUAIS, SUM(CASE WHEN CAST(D.DAV_DATA_EMISSAO AS DATE) BETWEEN ? AND ? THEN D.DAV_VALOR_LIQUIDO ELSE 0 END) AS VENDAS_ANTERIORES FROM DAV D WHERE D.DAV_TIPO='PD' AND D.DAV_SITUACAO IN(1,3) AND CAST(D.DAV_DATA_EMISSAO AS DATE) BETWEEN ? AND ? {$where}", array_merge([$filtros['data_inicial'], $filtros['data_final'], $anterior['data_inicial'], $anterior['data_final'], $anterior['data_inicial'], $filtros['data_final']], $bindings));
    }

    public function obterEvolucaoVendas(array $filtros)
    {
        list($where, $bindings) = $this->montarFiltroDav($filtros, 'D');
        $dados = collect($this->selecionar("SELECT CAST(D.DAV_DATA_EMISSAO AS DATE) AS PERIODO, COUNT(*) AS QTD_PEDIDOS, SUM(D.DAV_VALOR_LIQUIDO) AS VALOR_VENDIDO FROM DAV D WHERE D.DAV_TIPO='PD' AND D.DAV_SITUACAO IN(1,3) AND CAST(D.DAV_DATA_EMISSAO AS DATE) BETWEEN ? AND ? {$where} GROUP BY CAST(D.DAV_DATA_EMISSAO AS DATE) ORDER BY 1", array_merge([$filtros['data_inicial'], $filtros['data_final']], $bindings)));
        $dados->each(function ($item) {
            $item->ROTULO = Carbon::parse($item->PERIODO)->format('d/m');
        });
        return ['agrupamento' => 'diario', 'dados' => $dados];
    }

    public function obterVendasPorRepresentante(array $filtros)
    {
        list($where, $bindings) = $this->montarFiltroDav($filtros, 'D');
        return collect($this->selecionar("SELECT D.PART_REPRESENTANTE_CODIGO AS COD_REP, MAX(P.PART_NOME) AS REPRESENTANTE, COUNT(*) AS QTD_PEDIDOS, SUM(D.DAV_VALOR_LIQUIDO) AS VALOR_VENDIDO FROM DAV D LEFT JOIN PARTICIPANTE P ON P.PART_REPRESENTANTE_CODIGO=D.PART_REPRESENTANTE_CODIGO WHERE D.DAV_TIPO='PD' AND D.DAV_SITUACAO IN(1,3) AND CAST(D.DAV_DATA_EMISSAO AS DATE) BETWEEN ? AND ? {$where} GROUP BY D.PART_REPRESENTANTE_CODIGO ORDER BY 4 DESC", array_merge([$filtros['data_inicial'], $filtros['data_final']], $bindings)));
    }

    public function obterSituacoesPedidos(array $filtros)
    {
        list($where, $bindings) = $this->montarFiltroDav($filtros, 'D');
        return collect($this->selecionar("SELECT D.DAV_SITUACAO, CASE D.DAV_SITUACAO WHEN 0 THEN 'PENDENTE' WHEN 1 THEN 'CONFIRMADO' WHEN 2 THEN 'CANCELADO' WHEN 3 THEN 'FATURADO' WHEN 9 THEN 'SUBSTITUÍDO' ELSE 'OUTRO' END AS SITUACAO, COUNT(*) AS QTD_PEDIDOS, SUM(D.DAV_VALOR_LIQUIDO) AS VALOR_PEDIDOS FROM DAV D WHERE D.DAV_TIPO='PD' AND CAST(D.DAV_DATA_EMISSAO AS DATE) BETWEEN ? AND ? {$where} GROUP BY D.DAV_SITUACAO ORDER BY 1", array_merge([$filtros['data_inicial'], $filtros['data_final']], $bindings)));
    }

    public function obterMaioresClientes(array $filtros)
    {
        list($where, $bindings) = $this->montarFiltroDav($filtros, 'D');
        return collect($this->selecionar("SELECT FIRST 10 D.PART_CODIGO AS COD_CLIENTE, MAX(P.PART_NOME) AS CLIENTE, COUNT(*) AS QTD_PEDIDOS, SUM(D.DAV_VALOR_LIQUIDO) AS VALOR_VENDIDO, SUM(D.DAV_VALOR_LIQUIDO)/COUNT(*) AS TICKET_MEDIO FROM DAV D LEFT JOIN PARTICIPANTE P ON P.PART_CODIGO=D.PART_CODIGO WHERE D.DAV_TIPO='PD' AND D.DAV_SITUACAO IN(1,3) AND CAST(D.DAV_DATA_EMISSAO AS DATE) BETWEEN ? AND ? {$where} GROUP BY D.PART_CODIGO ORDER BY 4 DESC", array_merge([$filtros['data_inicial'], $filtros['data_final']], $bindings)));
    }

    public function obterTopProdutos(array $filtros)
    {
        list($where, $bindings) = $this->montarFiltroDav($filtros, 'D');
        return collect($this->selecionar("SELECT FIRST 10 I.PRD_CODIGO, MAX(P.PRD_DESCRICAO) AS PRODUTO, MAX(I.DAVI_UM) AS UNIDADE, SUM(I.DAVI_QTDE) AS QUANTIDADE_VENDIDA, SUM(I.DAVI_VL_TOTAL) AS VALOR_VENDIDO, CASE WHEN SUM(I.DAVI_QTDE)>0 THEN SUM(I.DAVI_VL_TOTAL)/SUM(I.DAVI_QTDE) ELSE 0 END AS PRECO_MEDIO FROM DAV_ITENS I INNER JOIN DAV D ON D.DAV_NUMERO=I.DAV_NUMERO AND D.DAV_TIPO=I.DAV_TIPO LEFT JOIN PRODUTOS P ON P.PRD_CODIGO=I.PRD_CODIGO WHERE D.DAV_TIPO='PD' AND D.DAV_SITUACAO IN(1,3) AND I.DAVI_CANCELADO='N' AND CAST(D.DAV_DATA_EMISSAO AS DATE) BETWEEN ? AND ? {$where} GROUP BY I.PRD_CODIGO ORDER BY 5 DESC", array_merge([$filtros['data_inicial'], $filtros['data_final']], $bindings)));
    }

    protected function montarFiltroDav(array $filtros, $alias)
    {
        $sql = '';
        $bindings = [];
        if (!empty($filtros['rep_codigo'])) {
            $sql .= " AND {$alias}.PART_REPRESENTANTE_CODIGO = ?";
            $bindings[] = (int) $filtros['rep_codigo'];
        }
        if (!empty($filtros['part_codigo'])) {
            $sql .= " AND {$alias}.PART_CODIGO = ?";
            $bindings[] = (int) $filtros['part_codigo'];
        }
        return [$sql, $bindings];
    }

    protected function montarMemoriaCalculo(
        array $filtros,
        $financeiro,
        $previsaoCaixa,
        $inadimplencia,
        $comercial,
        $comparativoVendas,
        $evolucaoVendas,
        Collection $vendasRepresentante,
        Collection $tiposPagamento,
        Collection $situacoesPedidos,
        Collection $titulosVencidos,
        Collection $clientes,
        Collection $produtos
    ) {
        $filtrosAplicados = [
            'data_inicial' => $filtros['data_inicial'],
            'data_final' => $filtros['data_final'],
            'vencimento_inicial' => $filtros['vencimento_inicial'],
            'vencimento_final' => $filtros['vencimento_final'],
            'rep_codigo' => $filtros['rep_codigo'] ?? null,
            'part_codigo' => $filtros['part_codigo'] ?? null,
            'usuario_restrito' => !empty($filtros['usuario_restrito']),
        ];

        return [
            'vendas' => [
                'formula' => 'SUM(DAV.DAV_VALOR_LIQUIDO)',
                'regras' => ["DAV_TIPO = 'PD'", 'DAV_SITUACAO IN (1, 3)', 'DAV_DATA_EMISSAO entre data inicial e final'],
                'filtros' => $filtrosAplicados,
                'composicao' => [
                    'valor_periodo' => (float) ($comercial->VENDAS_PERIODO ?? 0),
                    'quantidade_pedidos' => (int) ($comercial->QTD_PEDIDOS_VALIDOS ?? 0),
                    'valor_periodo_anterior' => (float) ($comparativoVendas->VENDAS_ANTERIORES ?? 0),
                ],
            ],
            'receber' => [
                'formula' => 'DUPLICATAS - CRÉDITOS + CHEQUES = TOTAL A RECEBER',
                'abrangencia' => 'Toda a carteira em aberto, posição atual',
                'regras' => ['Não utiliza o período comercial selecionado', 'Duplicatas e cheques consolidados na posição atual', 'Duplicatas: CON_TIPO = 0 entram positivas; créditos: CON_TIPO = 2 entram negativos; CON_PREVISAO = 0; CON_SITUACAO IN (0, 1)', 'Cheques: CHQ_TERC = 0, CHQ_SITUACAO = 0 e conta vinculada'],
                'filtros' => [
                    'rep_codigo' => $filtros['rep_codigo'] ?? null,
                    'part_codigo' => $filtros['part_codigo'] ?? null,
                    'usuario_restrito' => !empty($filtros['usuario_restrito']),
                ],
                'composicao' => [
                    'valor_original' => (float) ($financeiro->RECEBER_VALOR_ORIGINAL ?? 0),
                    'valor_pago' => (float) ($financeiro->RECEBER_VALOR_PAGO ?? 0),
                    'juros' => (float) ($financeiro->RECEBER_VALOR_JUROS ?? 0),
                    'multa' => (float) ($financeiro->RECEBER_VALOR_MULTA ?? 0),
                    'desconto' => (float) ($financeiro->RECEBER_VALOR_DESCONTO ?? 0),
                    'outras_despesas' => (float) ($financeiro->RECEBER_OUTRAS_DESPESAS ?? 0),
                    'saldo_atualizado' => (float) ($financeiro->RECEBER_SALDO_ATUALIZADO ?? 0),
                    'duplicatas_bruto' => (float) ($financeiro->TOTAL_RECEBER_DUPLICATAS_BRUTO ?? 0),
                    'creditos' => -(float) ($financeiro->TOTAL_RECEBER_CREDITOS ?? 0),
                    'duplicatas' => (float) ($financeiro->TOTAL_RECEBER_DUPLICATAS ?? 0),
                    'cheques' => (float) ($financeiro->TOTAL_RECEBER_CHEQUES ?? 0),
                    'total' => (float) ($financeiro->TOTAL_RECEBER ?? 0),
                    'vencido_duplicatas' => (float) ($financeiro->TOTAL_RECEBER_VENCIDO_DUPLICATAS ?? 0),
                    'vencido_cheques' => (float) ($financeiro->TOTAL_RECEBER_VENCIDO_CHEQUES ?? 0),
                    'vencido_total' => (float) ($financeiro->TOTAL_RECEBER_VENCIDO ?? 0),
                    'a_vencer_duplicatas' => (float) ($financeiro->TOTAL_RECEBER_A_VENCER_DUPLICATAS ?? 0),
                    'a_vencer_cheques' => (float) ($financeiro->TOTAL_RECEBER_A_VENCER_CHEQUES ?? 0),
                    'a_vencer_total' => (float) ($financeiro->TOTAL_RECEBER_A_VENCER ?? 0),
                    'quantidade_duplicatas' => (int) ($financeiro->QTD_TITULOS_RECEBER_DUPLICATAS ?? 0),
                    'quantidade_cheques' => (int) ($financeiro->QTD_TITULOS_RECEBER_CHEQUES ?? 0),
                    'quantidade_total' => (int) ($financeiro->QTD_TITULOS_RECEBER ?? 0),
                ],
            ],
            'pagar' => [
                'formula' => 'SUM(FIN_CONTAS.CON_VALOR_CORRIGIDO)',
                'regras' => ['CON_TIPO = 1', 'CON_PREVISAO = 0', 'CON_SITUACAO IN (0, 1)', 'CON_VALOR_CORRIGIDO > 0'],
                'filtros' => $filtrosAplicados,
                'composicao' => [
                    'vencido' => (float) ($financeiro->TOTAL_PAGAR_VENCIDO ?? 0),
                    'a_vencer' => (float) ($financeiro->TOTAL_PAGAR_A_VENCER ?? 0),
                    'total' => (float) ($financeiro->TOTAL_PAGAR ?? 0),
                    'quantidade' => (int) ($financeiro->QTD_TITULOS_PAGAR ?? 0),
                ],
            ],
            'saldo' => [
                'formula' => '(DUPLICATAS + CHEQUES) - CONTAS_A_PAGAR',
                'filtros' => $filtrosAplicados,
                'composicao' => [
                    'duplicatas' => (float) ($financeiro->TOTAL_RECEBER_DUPLICATAS ?? 0),
                    'cheques' => (float) ($financeiro->TOTAL_RECEBER_CHEQUES ?? 0),
                    'total_receber' => (float) ($financeiro->TOTAL_RECEBER ?? 0),
                    'total_pagar' => (float) ($financeiro->TOTAL_PAGAR ?? 0),
                    'saldo_projetado' => (float) ($financeiro->SALDO_PROJETADO ?? 0),
                ],
            ],
            'inadimplencia' => [
                'formula' => '(DUPLICATAS_VENCIDAS + CHEQUES_VENCIDOS) / (DUPLICATAS_TOTAL_PERIODO + CHEQUES_TOTAL_PERIODO) * 100',
                'regras' => ['Denominador: todos os títulos com vencimento no período, pagos ou não', 'Numerador: títulos vencidos até hoje e ainda não pagos', 'Duplicatas pagas permanecem apenas na base pelo CON_VALOR_ORIGINAL', 'Cheques pagos permanecem apenas na base pelo CHQ_VALOR'],
                'filtros' => $filtrosAplicados,
                'composicao' => [
                    'vencido_duplicatas' => (float) ($inadimplencia->SALDO_VENCIDO_DUPLICATAS ?? 0),
                    'vencido_cheques' => (float) ($inadimplencia->SALDO_VENCIDO_CHEQUES ?? 0),
                    'vencido_total' => (float) ($inadimplencia->SALDO_VENCIDO ?? 0),
                    'total_duplicatas' => (float) ($inadimplencia->SALDO_TOTAL_DUPLICATAS ?? 0),
                    'total_cheques' => (float) ($inadimplencia->SALDO_TOTAL_CHEQUES ?? 0),
                    'total_periodo' => (float) ($inadimplencia->SALDO_TOTAL_PERIODO ?? 0),
                    'percentual' => (float) ($inadimplencia->PERCENTUAL_INADIMPLENCIA ?? 0),
                ],
            ],
            'caixa' => [
                'formula' => '(DUPLICATAS POR CON_DT_VENCIMENTO + CHEQUES POR CHQ_BOM_PARA) - CONTAS_A_PAGAR',
                'filtros' => $filtrosAplicados,
                'composicao' => [
                    'hoje' => $this->memoriaFaixaCaixa($previsaoCaixa, 'HOJE'),
                    'sete_dias' => $this->memoriaFaixaCaixa($previsaoCaixa, '7_DIAS'),
                    'trinta_dias' => $this->memoriaFaixaCaixa($previsaoCaixa, '30_DIAS'),
                ],
            ],
            'tipos_pagamento' => [
                'formula' => 'SUM(VALOR_CORRIGIDO) GROUP BY TIPO_PGTO',
                'regras' => ["Cheques recebem TIPO_PGTO = 'CHEQUE'", 'Duplicatas usam FIN_CONTAS_TIPOS.CON_TIPO_DESC'],
                'filtros' => $filtrosAplicados,
                'composicao' => $tiposPagamento->map(function ($item) {
                    return [
                        'tipo' => $item->TIPO_PAGAMENTO,
                        'quantidade' => (int) ($item->QTD_TITULOS ?? 0),
                        'valor' => (float) ($item->SALDO_RECEBER ?? 0),
                        'percentual' => (float) ($item->PERCENTUAL ?? 0),
                    ];
                })->values(),
            ],
            'evolucao' => [
                'formula' => 'SUM(DAV_VALOR_LIQUIDO) GROUP BY DAV_DATA_EMISSAO',
                'filtros' => $filtrosAplicados,
                'composicao' => [
                    'agrupamento' => $evolucaoVendas['agrupamento'] ?? 'diario',
                    'quantidade_pontos' => count($evolucaoVendas['dados'] ?? []),
                    'valor_total' => (float) ($comercial->VENDAS_PERIODO ?? 0),
                ],
            ],
            'representantes' => [
                'formula' => 'SUM(DAV_VALOR_LIQUIDO) GROUP BY PART_REPRESENTANTE_CODIGO',
                'filtros' => $filtrosAplicados,
                'composicao' => $vendasRepresentante,
            ],
            'situacoes' => [
                'formula' => 'COUNT(*) e SUM(DAV_VALOR_LIQUIDO) GROUP BY DAV_SITUACAO',
                'regras' => ['0 = Pendente', '1 = Confirmado', '2 = Cancelado', '3 = Faturado', '9 = Substituído'],
                'filtros' => $filtrosAplicados,
                'composicao' => $situacoesPedidos,
            ],
            'titulos_vencidos' => [
                'formula' => 'DUPLICATAS VENCIDAS UNION ALL CHEQUES VENCIDOS, ORDER BY VALOR_CORRIGIDO DESC, FIRST 10',
                'filtros' => $filtrosAplicados,
                'quantidade_exibida' => $titulosVencidos->count(),
            ],
            'pedidos_pendentes' => [
                'formula' => "SUM(DAV_VALOR_LIQUIDO) WHERE DAV_SITUACAO = 0 AND DAV_TIPO = 'PD'",
                'filtros' => $filtrosAplicados,
                'composicao' => ['quantidade' => (int) ($comercial->QTD_PEDIDOS_PENDENTES ?? 0), 'valor' => (float) ($comercial->VALOR_PEDIDOS_PENDENTES ?? 0)],
            ],
            'ticket_medio' => [
                'formula' => 'VENDAS_PERIODO / QTD_PEDIDOS_VALIDOS',
                'filtros' => $filtrosAplicados,
                'composicao' => ['vendas' => (float) ($comercial->VENDAS_PERIODO ?? 0), 'pedidos' => (int) ($comercial->QTD_PEDIDOS_VALIDOS ?? 0), 'ticket' => (float) ($comercial->TICKET_MEDIO ?? 0)],
            ],
            'clientes_atendidos' => [
                'formula' => 'COUNT(DISTINCT DAV.PART_CODIGO)',
                'filtros' => $filtrosAplicados,
                'composicao' => ['clientes' => (int) ($comercial->CLIENTES_ATENDIDOS ?? 0), 'pedidos' => (int) ($comercial->QTD_PEDIDOS_VALIDOS ?? 0)],
            ],
            'maiores_clientes' => ['formula' => 'FIRST 10 clientes ORDER BY VALOR_VENDIDO DESC', 'filtros' => $filtrosAplicados, 'quantidade_exibida' => $clientes->count()],
            'top_produtos' => ['formula' => 'FIRST 10 produtos ORDER BY VALOR_VENDIDO DESC', 'filtros' => $filtrosAplicados, 'quantidade_exibida' => $produtos->count()],
        ];
    }

    protected function memoriaFaixaCaixa($previsaoCaixa, $sufixo)
    {
        return [
            'duplicatas' => (float) ($previsaoCaixa->{'RECEBER_' . $sufixo . '_DUPLICATAS'} ?? 0),
            'cheques' => (float) ($previsaoCaixa->{'RECEBER_' . $sufixo . '_CHEQUES'} ?? 0),
            'receber' => (float) ($previsaoCaixa->{'RECEBER_' . $sufixo} ?? 0),
            'pagar' => (float) ($previsaoCaixa->{'PAGAR_' . $sufixo} ?? 0),
            'saldo' => (float) ($previsaoCaixa->{'SALDO_' . $sufixo} ?? 0),
        ];
    }

    protected function normalizarFiltros(array $filtros)
    {
        $filtros['data_inicial'] = !empty($filtros['data_inicial']) ? Carbon::parse($filtros['data_inicial'])->format('Y-m-d') : Carbon::today()->startOfMonth()->format('Y-m-d');
        $filtros['data_final'] = !empty($filtros['data_final']) ? Carbon::parse($filtros['data_final'])->format('Y-m-d') : Carbon::today()->format('Y-m-d');
        $filtros['vencimento_inicial'] = !empty($filtros['vencimento_inicial']) ? Carbon::parse($filtros['vencimento_inicial'])->format('Y-m-d') : Carbon::today()->subDays(90)->format('Y-m-d');
        $filtros['vencimento_final'] = !empty($filtros['vencimento_final']) ? Carbon::parse($filtros['vencimento_final'])->format('Y-m-d') : Carbon::today()->addDays(90)->format('Y-m-d');
        if (Carbon::parse($filtros['vencimento_inicial'])->gt(Carbon::parse($filtros['vencimento_final']))) {
            $temporaria = $filtros['vencimento_inicial'];
            $filtros['vencimento_inicial'] = $filtros['vencimento_final'];
            $filtros['vencimento_final'] = $temporaria;
        }
        $filtros['rep_codigo'] = $filtros['rep_codigo'] ?? null;
        $filtros['part_codigo'] = $filtros['part_codigo'] ?? null;
        return $filtros;
    }

    protected function calcularPeriodoAnterior($dataInicial, $dataFinal)
    {
        $inicio = Carbon::parse($dataInicial);
        $fim = Carbon::parse($dataFinal);
        $dias = $inicio->diffInDays($fim) + 1;
        $fimAnterior = $inicio->copy()->subDay();
        $inicioAnterior = $fimAnterior->copy()->subDays($dias - 1);
        return ['data_inicial' => $inicioAnterior->format('Y-m-d'), 'data_final' => $fimAnterior->format('Y-m-d'), 'dias' => $dias];
    }

    protected function selecionar($sql, array $bindings = [])
    {
        return DB::connection($this->connection)->select($sql, $bindings);
    }
    protected function primeiro($sql, array $bindings = [])
    {
        $resultado = $this->selecionar($sql, $bindings);
        return !empty($resultado) ? $resultado[0] : (object) [];
    }
}
