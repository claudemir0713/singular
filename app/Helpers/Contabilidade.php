<?php
namespace App\Helpers;

use App\Models\FIN_CONTAS;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Contabilidade {
    public static function baixa($dataI,$dataF,$tipo) {
        $sql = "
            SELECT
                TIPO
                , DATA
                , DOCUMENTO
                , CONSIDERA
                , D
                , C
                , COD_HIST
                , HISTORICO
                , SUM(VALOR) AS VALOR
            FROM(
                SELECT
                    CASE
                        WHEN FC.CON_TIPO = 0 THEN 'C'
                        WHEN FC.CON_TIPO = 1 THEN 'F'
                        ELSE 'VERIFICAR'
                    END 																			AS TIPO
                    ,cast(CX.CXM_DATAHORA as date)													AS DATA
                    ,FC.CON_NUMERO																	AS DOCUMENTO
                    ,CASE
                        WHEN TRIM(FC.CON_NUMERO) LIKE '%/CH' THEN 'N'
                        WHEN TRIM(FC.CON_NUMERO) LIKE '%/PD' THEN 'N'
                        WHEN TRIM(FC.CON_NUMERO) LIKE '%/AF' THEN 'N'
                        ELSE 'S'
                    END																				AS CONSIDERA
                    , CASE
                        WHEN COALESCE(DESP.CEN_CODIGO,0) IN (346) THEN 958  -- TARIFAS
                        ELSE COALESCE(CLI.PART_CNPJ_CPF, FORN.PART_CNPJ_CPF, REP.PART_CNPJ_CPF)
                    END                                                                             AS D
                    ,CASE
                        WHEN  CX_DESCRICAO LIKE 'CAIXA' 		THEN 25 --'Caixa'
                        WHEN  CX_DESCRICAO LIKE 'DEPOSITO%IDE%'	THEN 349 --'Adto'
                        WHEN  CX_DESCRICAO LIKE '%BRADESCO%' 	THEN 36 --'Banco Bradesco'
                        WHEN  CX_DESCRICAO LIKE 'BANCO%BRASIL%' THEN 35 --'Banco do Brasil '
                        WHEN  CX_DESCRICAO LIKE 'BANCO%ITA%' 	THEN 34 --'Banco Itaú'
                        WHEN  CX_DESCRICAO LIKE '%SICOOB%' 		THEN 39 --'Banco Sicoob'
                        ELSE  CX_DESCRICAO
                    END																				AS C
                    ,CASE
                        WHEN CX_DESCRICAO LIKE 'DEPOSITO%IDE%'	THEN 529 --'Adto'
                        WHEN COALESCE(DESP.CEN_CODIGO,0) IN (346) THEN 388 -- TARIFAS
                        WHEN FC.CON_TIPO = 0 THEN 22
                        WHEN FC.CON_TIPO = 1 THEN 6
                        ELSE 'VERIFICAR'
                    END																				AS COD_HIST
                    ,CASE
                        WHEN COALESCE(DESP.CEN_CODIGO,0) IN (346) THEN 'TARIFA BANCÁRIA'
                        ELSE CX.CXM_DESCRICAO||' #'||FC.CON_CODIGO
                    END                                                                             AS HISTORICO
                    ,CXM_VALOR_TOTAL*COALESCE((RATE.CCL_PERCENTUAL/100),1)							AS VALOR

                FROM CAIXA_MOVIMENTO  AS CX
                LEFT JOIN CAIXA ON CAIXA.CX_CODIGO = CX.CX_CODIGO
                INNER JOIN FIN_CONTAS_PAGAMENTOS AS FCP ON FCP.CCP_CODIGO = CX.CCP_CODIGO
                LEFT JOIN FIN_CONTAS AS FC ON FC.CON_CODIGO = FCP.CON_CODIGO
                LEFT JOIN PARTICIPANTE AS CLI ON CLI.PART_CLIENTE_CODIGO = FC.ENT_CODIGO
                LEFT JOIN PARTICIPANTE AS FORN ON FORN.PART_FORNECEDOR_CODIGO = FC.ENT_CODIGO
                LEFT JOIN PARTICIPANTE AS REP ON REP.PART_REPRESENTANTE_CODIGO = FC.ENT_CODIGO
                LEFT JOIN CENTRO_CUSTO_LANCAMENTOS AS RATE ON RATE.CON_CODIGO = FC.CON_CODIGO
                LEFT JOIN CENTRO_CUSTO AS DESP ON DESP.CEN_CODIGO = RATE.CEN_CODIGO

                WHERE  CX.CCP_CODIGO >0
                AND CX_FLUXO_CAIXA = 1
                --AND COALESCE(DESP.CEN_CODIGO,0) NOT IN (346)
                and DESP.CEN_MASCARA not in ('3.46')
                AND CX.CXM_DATAHORA BETWEEN '$dataI' AND '$dataF'
                AND FC.CON_TIPO = $tipo
                ORDER BY
                    FC.CON_TIPO
                    ,CXM_DATAHORA
                    ,FC.CON_NUMERO
            )BAIXA
            WHERE CONSIDERA = 'S'
            GROUP BY
                TIPO
                , DATA
                , DOCUMENTO
                , CONSIDERA
                , D
                , C
                , COD_HIST
                , HISTORICO

        ";
        // dd($sql);
        $baixas = DB::connection(env('APP_NAME'))->select($sql);

        if($tipo==2){
            $sql_transf = "
                SELECT
                    TIPO
                    , DATA
                    , DOCUMENTO
                    , CONSIDERA
                    , CASE
                        WHEN  D LIKE 'CAIXA' 			THEN 25 --'Caixa'
                        WHEN  D LIKE 'DEPOSITO%IDE%'	THEN 349 --'Adto'
                        WHEN  D LIKE '%BRADESCO%' 		THEN 36 --'Banco Bradesco'
                        WHEN  D LIKE 'BANCO%BRASIL%' 	THEN 35 --'Banco do Brasil '
                        WHEN  D LIKE 'BANCO%ITA%' 		THEN 34 --'Banco Itaú'
                        WHEN  D LIKE '%SICOOB%' 		THEN 39 --'Banco Sicoob'
                        ELSE  D
                    END D
                    , CASE
                        WHEN  C LIKE 'CAIXA' 			THEN 25 --'Caixa'
                        WHEN  C LIKE 'DEPOSITO%IDE%'	THEN 349 --'Adto'
                        WHEN  C LIKE '%BRADESCO%' 		THEN 36 --'Banco Bradesco'
                        WHEN  C LIKE 'BANCO%BRASIL%' 	THEN 35 --'Banco do Brasil '
                        WHEN  C LIKE 'BANCO%ITA%' 		THEN 34 --'Banco Itaú'
                        WHEN  C LIKE '%SICOOB%' 		THEN 39 --'Banco Sicoob'
                        ELSE  C
                    END  C
                    , COD_HIST
                    , HISTORICO
                    , VALOR
                FROM(
                    SELECT
                        'F' 			                    AS TIPO
                        , CAST(CXM_DATAHORA as DATE) 	    AS DATA
                        , CAST(CXM_MOVIMENTO AS CHAR(20))   AS DOCUMENTO
                        , 'S' 			                    AS CONSIDERA
                        , CX_DESCRICAO	                    AS D
                        , (SELECT first 1 CAIXA.CX_DESCRICAO  FROM CAIXA_MOVIMENTO AS CXC LEFT JOIN CAIXA ON CAIXA.CX_CODIGO = CXC.CX_CODIGO WHERE CXC.CXM_TRANSFERENCIA = CAIXA_MOVIMENTO.CXM_TRANSFERENCIA  AND CTR_CODIGO IN (6)) AS C
                        ,'66'  				                AS COD_HIST
                        ,CXM_DESCRICAO  	                AS HISTORICO
                        ,CXM_VALOR_TOTAL  	                AS VALOR

                    FROM CAIXA_MOVIMENTO
                    LEFT JOIN CAIXA ON CAIXA.CX_CODIGO = CAIXA_MOVIMENTO.CX_CODIGO
                    WHERE CTR_CODIGO IN (5)
                    AND CXM_DATAHORA BETWEEN '$dataI' AND '$dataF'
                )TRANSF
            ";

            $transf = DB::connection(env('APP_NAME'))->select($sql_transf);
            $baixas = array_merge($baixas,$transf);
        }
        // dd($baixas);
        return $baixas;
    }

}
