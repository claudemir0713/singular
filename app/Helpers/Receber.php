<?php
namespace App\Helpers;

use App\Models\FIN_CONTAS;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Receber {
    public static function receber($dataI,$dataF,$cliente,$vendedor,$tipo,$status,$nf,$dtIEmissa,$dtFEmissa,$nivel,$rep_cod) {
        $rep_cod1 = '';
        if($nivel=='restrito'){
            $rep_cod1 = " AND REP.PART_CODIGO  = $rep_cod";
        }

        if($dataI){
            $dataI_ch = " AND CHQ_BOM_PARA >= '$dataI'";
            $dataI = " AND CON_DT_VENCIMENTO >= '$dataI'";
        }else{
            $dataI ='';
            $dataI_ch ='';
        };
        if($dataF){
            $dataF_ch = " AND CHQ_BOM_PARA <= '$dataF'";
            $dataF = " AND CON_DT_VENCIMENTO <= '$dataF'";
        }else{
            $dataF ='';
            $dataF_ch ='';
        }

        if($dtIEmissa){
            $dtIEmissa_ch = " AND CHQ_LANCAMENTO >= '$dtIEmissa'";
            $dtIEmissa = " AND CON_DT_INCLUSAO >= '$dtIEmissa'";
        }else{
            $dtIEmissa ='';
            $dtIEmissa_ch ='';
        };
        if($dtFEmissa){
            $dtFEmissa_ch = " AND CHQ_LANCAMENTO <= '$dtFEmissa'";
            $dtFEmissa = " AND CON_DT_INCLUSAO <= '$dtFEmissa'";
        }else{
            $dtFEmissa ='';
            $dtFEmissa_ch ='';
        }

        if($cliente){
            $cliente = " AND CLI.PART_NOME like '%$cliente%'";
        }else{
            $cliente ='';
        }
        if($vendedor){
            $vendedor = " AND REP.PART_NOME like '%$vendedor%'";
        }else{
            $vendedor ='';
        }
        if($tipo){
            if($tipo=='PD'){
                $tipo = " AND RIGHT(TRIM(CON_NUMERO),2) IN ('PD','CH')";
                $tipo_ch = " AND CHEQUE.CHQ_CODIGO is null";
            }elseif($tipo=='CH'){
                $tipo = " AND RIGHT(TRIM(CON_NUMERO),2) IN ('SEM')";
                $tipo_ch = " AND CHEQUE.CHQ_CODIGO is not null";
            }elseif($tipo!='PD'){
                $tipo = " AND RIGHT(TRIM(CON_NUMERO),2) NOT IN ('PD','CH')";
                $tipo_ch = " AND CHEQUE.CHQ_CODIGO is null";
            }
        }else{
            $tipo = '';
            $tipo_ch ='';
        }
        if($status){
            $status = " AND FIN_CONTAS.CON_SITUACAO in ($status)";
        }else{
            $status = " AND FIN_CONTAS.CON_SITUACAO in (0,1)";
        }

        if($nf){
            $ch = " AND CHQ_CHEQUE like '$nf%'";
            $nf = " AND FIN_CONTAS.CON_NUMERO like '$nf%'";
            $status     = '';
            $dataI      = '';
            $dataF      = '';
            $cliente    = '';
            $vendedor   = '';
            $tipo       = '';
        }else{
            $nf ='';
            $ch ='';
        }

        $sql="
            select
                *
            from(
                select
                    FIN_CONTAS.CON_CODIGO
                    ,FIN_CONTAS.CON_TIPO
                    ,FIN_CONTAS.CON_NUMERO
                    ,FIN_CONTAS.CON_SEQUENCIA
                    ,FIN_CONTAS.CON_DT_INCLUSAO
                    ,FIN_CONTAS.CON_DT_PAGAMENTO
                    ,FIN_CONTAS.CON_DT_VENCIMENTO
                    ,CON_VALOR_ORIGINAL
                    ,FIN_CONTAS.CON_VALOR_JUROS
                    ,FIN_CONTAS.CON_VALOR_MULTA
                    ,FIN_CONTAS.CON_VALOR_DESCONTO
                    ,FIN_CONTAS.CON_VALOR_OUTRASDESPESAS
                    ,FIN_CONTAS.CON_VALOR_TOTAL_PAGO
                    ,FIN_CONTAS.CON_VALOR_CORRIGIDO
                    ,FIN_CONTAS.CON_BC_COMISSAO
                    ,FIN_CONTAS.CON_SITUACAO
                    ,CLI.PART_CLIENTE_CODIGO				AS COD_CLI
                    ,CLI.PART_NOME							AS CLIENTE
                    ,REP.PART_REPRESENTANTE_CODIGO			AS COD_REP
                    ,REP.PART_NOME							AS REPRESENTANTE
                    ,'DUPLICATA'                            AS ORIGEM
                    ,CON_TIPO_DESC                          AS TIPO_PGTO


                FROM FIN_CONTAS
                LEFT JOIN FIN_CONTAS_COMISSAO ON FIN_CONTAS_COMISSAO.CON_CODIGO = FIN_CONTAS.CON_CODIGO
                LEFT JOIN PARTICIPANTE AS CLI on CLI.PART_CODIGO = FIN_CONTAS.PART_CODIGO
                LEFT JOIN PARTICIPANTE AS REP ON REP.PART_REPRESENTANTE_CODIGO = FIN_CONTAS_COMISSAO.PART_REPRESENTANTE_CODIGO
                LEFT JOIN Fin_contas_tipos AS TIPO_PGTO ON TIPO_PGTO.CON_TIPO_PAGAMENTO = FIN_CONTAS.CON_TIPO_PAGAMENTO

                WHERE CON_TIPO = 0
                AND FIN_CONTAS.CON_PREVISAO = 0
                $nf
                $status
                $dtIEmissa
                $dtFEmissa
                $dataI
                $dataF
                $cliente
                $vendedor
                $tipo
                $rep_cod1

                GROUP BY
                    FIN_CONTAS.CON_CODIGO
                    ,FIN_CONTAS.CON_TIPO
                    ,FIN_CONTAS.CON_NUMERO
                    ,FIN_CONTAS.CON_SEQUENCIA
                    ,FIN_CONTAS.CON_DT_INCLUSAO
                    ,FIN_CONTAS.CON_DT_PAGAMENTO
                    ,FIN_CONTAS.CON_DT_VENCIMENTO
                    ,CON_VALOR_ORIGINAL
                    ,FIN_CONTAS.CON_VALOR_JUROS
                    ,FIN_CONTAS.CON_VALOR_MULTA
                    ,FIN_CONTAS.CON_VALOR_DESCONTO
                    ,FIN_CONTAS.CON_VALOR_OUTRASDESPESAS
                    ,FIN_CONTAS.CON_VALOR_TOTAL_PAGO
                    ,FIN_CONTAS.CON_VALOR_CORRIGIDO
                    ,FIN_CONTAS.CON_BC_COMISSAO
                    ,FIN_CONTAS.CON_SITUACAO
                    ,CLI.PART_CLIENTE_CODIGO
                    ,CLI.PART_NOME
                    ,REP.PART_REPRESENTANTE_CODIGO
                    ,REP.PART_NOME
                    ,CON_TIPO_DESC

                UNION ALL
                select
                    FIN_CONTAS.CON_CODIGO
                    ,FIN_CONTAS.CON_TIPO
                    ,FIN_CONTAS.CON_NUMERO
                    ,FIN_CONTAS.CON_SEQUENCIA
                    ,FIN_CONTAS.CON_DT_INCLUSAO
                    ,FIN_CONTAS.CON_DT_PAGAMENTO
                    ,FIN_CONTAS.CON_DT_VENCIMENTO
                    ,-CON_VALOR_ORIGINAL
                    ,-FIN_CONTAS.CON_VALOR_JUROS
                    ,-FIN_CONTAS.CON_VALOR_MULTA
                    ,-FIN_CONTAS.CON_VALOR_DESCONTO
                    ,-FIN_CONTAS.CON_VALOR_OUTRASDESPESAS
                    ,-FIN_CONTAS.CON_VALOR_TOTAL_PAGO
                    ,-FIN_CONTAS.CON_VALOR_CORRIGIDO
                    ,-FIN_CONTAS.CON_BC_COMISSAO
                    ,FIN_CONTAS.CON_SITUACAO
                    ,CLI.PART_CLIENTE_CODIGO				AS COD_CLI
                    ,CLI.PART_NOME							AS CLIENTE
                    ,REP.PART_REPRESENTANTE_CODIGO			AS COD_REP
                    ,REP.PART_NOME							AS REPRESENTANTE
                    ,'DUPLICATA'                            AS ORIGEM
                    ,CON_TIPO_DESC                          AS TIPO_PGTO


                FROM FIN_CONTAS
                LEFT JOIN FIN_CONTAS_COMISSAO ON FIN_CONTAS_COMISSAO.CON_CODIGO = FIN_CONTAS.CON_CODIGO
                LEFT JOIN PARTICIPANTE AS CLI on CLI.PART_CODIGO = FIN_CONTAS.PART_CODIGO
                LEFT JOIN PARTICIPANTE AS REP ON REP.PART_REPRESENTANTE_CODIGO = FIN_CONTAS_COMISSAO.PART_REPRESENTANTE_CODIGO
                LEFT JOIN Fin_contas_tipos AS TIPO_PGTO ON TIPO_PGTO.CON_TIPO_PAGAMENTO = FIN_CONTAS.CON_TIPO_PAGAMENTO

                WHERE CON_TIPO = 2
                AND FIN_CONTAS.CON_PREVISAO = 0
                $nf
                $status
                $dtIEmissa
                $dtFEmissa
                $dataI
                $dataF
                $cliente
                $vendedor
                $tipo
                $rep_cod1

                GROUP BY
                    FIN_CONTAS.CON_CODIGO
                    ,FIN_CONTAS.CON_TIPO
                    ,FIN_CONTAS.CON_NUMERO
                    ,FIN_CONTAS.CON_SEQUENCIA
                    ,FIN_CONTAS.CON_DT_INCLUSAO
                    ,FIN_CONTAS.CON_DT_PAGAMENTO
                    ,FIN_CONTAS.CON_DT_VENCIMENTO
                    ,CON_VALOR_ORIGINAL
                    ,FIN_CONTAS.CON_VALOR_JUROS
                    ,FIN_CONTAS.CON_VALOR_MULTA
                    ,FIN_CONTAS.CON_VALOR_DESCONTO
                    ,FIN_CONTAS.CON_VALOR_OUTRASDESPESAS
                    ,FIN_CONTAS.CON_VALOR_TOTAL_PAGO
                    ,FIN_CONTAS.CON_VALOR_CORRIGIDO
                    ,FIN_CONTAS.CON_BC_COMISSAO
                    ,FIN_CONTAS.CON_SITUACAO
                    ,CLI.PART_CLIENTE_CODIGO
                    ,CLI.PART_NOME
                    ,REP.PART_REPRESENTANTE_CODIGO
                    ,REP.PART_NOME
                    ,CON_TIPO_DESC

                union all

                SELECT
                    CHEQUE.CHQ_CODIGO						AS CON_CODIGO
                    ,0										AS CON_TIPO
                    ,'Nr.'||CHEQUE.CHQ_CHEQUE  			AS CON_NUMERO
                    ,0										AS CON_SEQUENCIA
                    ,CHEQUE.CHQ_LANCAMENTO 					AS CON_DT_INCLUSAO
                    ,CHEQUE.CHQ_BOM_PARA					AS CON_DT_PAGAMENTO
                    ,CHEQUE.CHQ_BOM_PARA					AS CON_DT_VENCIMENTO
                    ,CHEQUE.CHQ_VALOR						AS CON_VALOR_ORIGINAL
                    ,0										AS CON_VALOR_JUROS
                    ,0										AS CON_VALOR_MULTA
                    ,0 										AS CON_VALOR_DESCONTO
                    ,0										AS CON_VALOR_OUTRASDESPESAS
                    ,0										AS CON_VALOR_TOTAL_PAGO
                    ,CHEQUE.CHQ_VALOR						AS CON_VALOR_CORRIGIDO
                    ,CHEQUE.CHQ_VALOR						AS CON_BC_COMISSAO
                    ,CHEQUE.CHQ_SITUACAO					AS CON_SITUACAO
                    ,CLI.PART_CLIENTE_CODIGO				AS COD_CLI
                    ,CLI.PART_NOME							AS CLIENTE
                    ,REP.PART_REPRESENTANTE_CODIGO			AS COD_REP
                    ,REP.PART_NOME							AS REPRESENTANTE
                    ,'CHEQUE'                               AS ORIGEM
                    ,'CHEQUE'                               AS TIPO_PGTO


                FROM CHEQUE
                LEFT JOIN FIN_CONTAS_CHEQUES ON FIN_CONTAS_CHEQUES.CHQ_CODIGO = CHEQUE.CHQ_CODIGO
                LEFT JOIN FIN_CONTAS ON FIN_CONTAS.CON_CODIGO = FIN_CONTAS_CHEQUES.CON_CODIGO
                LEFT JOIN FIN_CONTAS_COMISSAO 	ON FIN_CONTAS_COMISSAO.CON_CODIGO 	= FIN_CONTAS.CON_CODIGO
                LEFT JOIN PARTICIPANTE AS CLI on CLI.PART_CODIGO = FIN_CONTAS.PART_CODIGO
                LEFT JOIN PARTICIPANTE AS REP ON REP.PART_REPRESENTANTE_CODIGO = FIN_CONTAS_COMISSAO.PART_REPRESENTANTE_CODIGO

                WHERE 1=1
                AND FIN_CONTAS.CON_TIPO = 0
                AND CHQ_TERC = 0
                AND FIN_CONTAS.CON_CODIGO IS NOT NULL
                AND CHQ_SITUACAO = 0
                $ch
                $dtIEmissa_ch
                $dtFEmissa_ch
                $tipo_ch
                $dataI_ch
                $dataF_ch
                $cliente
                $vendedor
                $rep_cod1


                GROUP BY
                    CHEQUE.CHQ_CODIGO
                    ,CHEQUE.CHQ_CHEQUE
                    ,CHEQUE.CHQ_LANCAMENTO
                    ,CHEQUE.CHQ_BOM_PARA
                    ,CHEQUE.CHQ_BOM_PARA
                    ,CHEQUE.CHQ_VALOR
                    ,CHEQUE.CHQ_VALOR
                    ,CHEQUE.CHQ_VALOR
                    ,CHEQUE.CHQ_SITUACAO
                    ,CLI.PART_CLIENTE_CODIGO
                    ,CLI.PART_NOME
                    ,REP.PART_REPRESENTANTE_CODIGO
                    ,REP.PART_NOME
            )dados
                ORDER BY
                    COD_REP
                    ,ORIGEM DESC
                    ,CON_DT_VENCIMENTO
                    ,CLIENTE
        ";
        // dd($sql);
        $receber = dB::connection(env('APP_NAME'))->select($sql);
        return $receber;
    }


}
