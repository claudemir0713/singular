<?php
namespace App\Helpers;

use App\Models\FIN_CONTAS;
use App\Models\FIN_CONTAS_COMISSAO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Comissao {
    public static function comissao($dataI,$dataF,$cliente,$vendedor,$nf) {
        // DB::connection()->enableQueryLog();
        $filtro = [];
        $filtro[]=['CON_ORIGEM','<>','A'];
        if($dataI){
            $filtro[] =['FIN_CONTAS.CON_DT_INCLUSAO','>=',$dataI];
        }
        if($dataF){
            $filtro[] =['FIN_CONTAS.CON_DT_INCLUSAO','<=',$dataF];
        }
        if($vendedor){
            $filtro[] =['REP.PART_NOME','LIKE','%'.$vendedor.'%'];
        }
        if($cliente){
            $filtro[] =['CLI.PART_NOME','LIKE','%'.$cliente.'%'];
        }
        if($nf){
            $filtro = [];
            $filtro[] =['FIN_CONTAS.CON_NUMERO','LIKE',$nf.'%'];
        }
        // dd( $filtro);
        $comissao = [];
        $comissao1 = FIN_CONTAS::leftJoin('FIN_CONTAS_COMISSAO','FIN_CONTAS_COMISSAO.CON_CODIGO','FIN_CONTAS.CON_CODIGO')
                                    ->leftJoin('PARTICIPANTE AS REP','REP.PART_REPRESENTANTE_CODIGO','FIN_CONTAS_COMISSAO.PART_REPRESENTANTE_CODIGO')
                                    ->leftJoin('PARTICIPANTE AS CLI','CLI.PART_CLIENTE_CODIGO','FIN_CONTAS.ENT_CODIGO')
                                    ->leftJoin('PARTICIPANTE AS TOM','TOM.PART_TOMADOR_CODIGO','FIN_CONTAS.ENT_CODIGO')
                                    ->where($filtro)
                                    ->where('CON_TIPO',0)
                                    ->whereNotIn('CON_ORIGEM',['T'])
                                    ->orderBy('REP.PART_NOME')
                                    ->orderBy('CON_DT_INCLUSAO')
                                    ->orderBy('CON_NUMERO')
                                    ->orderBy('CON_SEQUENCIA')
                                    ->get([
                                        DB::raw("REP.PART_NOME AS VENDEDOR")
                                        ,DB::raw("coalesce(CLI.PART_NOME,TOM.PART_NOME) AS CLIENTE")
                                        ,'FIN_CONTAS.CON_CODIGO'
                                        ,'FIN_CONTAS.CON_NUMERO'
                                        ,'FIN_CONTAS.CON_SEQUENCIA'
                                        ,'FIN_CONTAS.CON_DT_INCLUSAO'
                                        ,'FIN_CONTAS.CON_DT_VENCIMENTO'
                                        ,'FIN_CONTAS.CON_VALOR_ORIGINAL'
                                        ,'FIN_CONTAS.PART_CODIGO'
                                        ,'FIN_CONTAS.CON_BC_COMISSAO'
                                        ,'FIN_CONTAS_COMISSAO.CONC_CODIGO'
                                        ,'FIN_CONTAS_COMISSAO.CON_CODIGO'
                                        ,'FIN_CONTAS_COMISSAO.PART_REPRESENTANTE_CODIGO'
                                        ,'FIN_CONTAS_COMISSAO.CONC_PERC_COMISSAO'
                                        ,'FIN_CONTAS.CON_ORIGEM'
                                        ,'FIN_CONTAS.DOC_NUMERO_ORIGEM'
                                        ,db::raw("CASE WHEN CON_ORIGEM='N' AND CON_SEQUENCIA=1  THEN (SELECT SUM(NF_VL_IPI + NF_VL_FRETE) FROM NF_VENDA_CABECALHO WHERE DOC_NUMERO = FIN_CONTAS.DOC_NUMERO_ORIGEM)  END as VLR_IPI_FRETE")
                                        ,db::raw("CASE WHEN CON_ORIGEM='N' AND CON_SEQUENCIA=1  THEN (SELECT FIRST 1 DAV.DAV_VALOR_FRETE  FROM NF_VENDA_CABECALHO AS NF1  LEFT JOIN NF_VENDA_ITENS NFI1 ON NFI1.NF_NUMERO = NF1.NF_NUMERO LEFT JOIN DAV ON DAV.DAV_NUMERO = NFI1.PED_NUMERO WHERE NF1.DOC_NUMERO = FIN_CONTAS.DOC_NUMERO_ORIGEM GROUP BY DAV.DAV_VALOR_FRETE)  END as FRETE_NF_PEDIDO" )
                                        ,db::raw("CASE WHEN CON_ORIGEM='P' AND CON_SEQUENCIA=1  THEN (SELECT FIRST 1 DAV_VALOR_FRETE FROM DAV WHERE DOC_NUMERO = FIN_CONTAS.DOC_NUMERO_ORIGEM)  END as FRETE_PEDIDO" )
                                    ]);
        $nrDocumento = 0;
        foreach($comissao1 as $item){
            $FRETE_PEDIDO = $item->FRETE_PEDIDO;
            if($nrDocumento == str_replace('/NF','',str_replace('/PD','',$item->CON_NUMERO))){
                $FRETE_PEDIDO = 0;
            }

            $comissao[] = array(
                'VENDEDOR'                      => $item->VENDEDOR
                ,'CLIENTE'                      => $item->CLIENTE
                ,'CON_CODIGO'                   => $item->CON_CODIGO
                ,'CON_NUMERO'                   => $item->CON_NUMERO
                ,'CON_SEQUENCIA'                => $item->CON_SEQUENCIA
                ,'CON_DT_INCLUSAO'              => $item->CON_DT_INCLUSAO
                ,'CON_DT_VENCIMENTO'            => $item->CON_DT_VENCIMENTO
                ,'CON_VALOR_ORIGINAL'           => $item->CON_VALOR_ORIGINAL
                ,'PART_CODIGO'                  => $item->PART_CODIGO
                ,'CON_BC_COMISSAO'              => $item->CON_BC_COMISSAO
                ,'CONC_CODIGO'                  => $item->CONC_CODIGO
                ,'PART_REPRESENTANTE_CODIGO'    => $item->PART_REPRESENTANTE_CODIGO
                ,'CONC_PERC_COMISSAO'           => $item->CONC_PERC_COMISSAO
                ,'CON_ORIGEM'                   => $item->CON_ORIGEM
                ,'DOC_NUMERO_ORIGEM'            => $item->DOC_NUMERO_ORIGEM
                ,'VLR_IPI_FRETE'                => $item->VLR_IPI_FRETE
                ,'FRETE_NF_PEDIDO'              => $item->FRETE_NF_PEDIDO
                ,'FRETE_PEDIDO'                 => $FRETE_PEDIDO
            );
            $nrDocumento = str_replace('/NF','',str_replace('/PD','',$item->CON_NUMERO));

        }
        // $queries = DB::getQueryLog();
        // dd($queries);


        return ($comissao);
    }
    public static function comissaoPagar($dataI,$dataF,$cliente,$vendedor,$nf,$parcela,$nivel,$rep_cod) {
        $rep_cod1 = '';
        if($nivel=='restrito'){
            $rep_cod1 = " AND REP.PART_CODIGO  = $rep_cod";
        }

        $cliente    = " AND CLI.PART_NOME LIKE '$cliente%'";
        $vendedor   = " AND REP.PART_NOME LIKE '%$vendedor%'";
        $data       = " AND CCP_DT_PAGAMENTO BETWEEN '$dataI' AND '$dataF'";
        $dtCheq     = " AND CHEQUE.CHQ_BOM_PARA BETWEEN '$dataI' AND '$dataF'";
        if($parcela){
            $parcela    = " AND CON_SEQUENCIA = $parcela";
        };

        if($nf){
            $cliente    = '';
            $vendedor   = '';
            $data       = '';
            $dtCheq     = '';
            $nf         = " AND FIN_CONTAS.CON_NUMERO LIKE '$nf%'";
        }else{
            $nf         = '';
        }
        $sql ="
            SELECT
                VENDEDOR
                , CLIENTE
                , CON_CODIGO
                , CON_NUMERO
                , CON_SEQUENCIA
                , NR_CHEQUE
                , CON_DT_INCLUSAO
                , CON_DT_VENCIMENTO
                , DT_PAGAMENTO
                , VLR_PAGO
                , JUROS
                , MULTA
                , OUTRAS_DESPESAS AS DESPESA
                , CON_VALOR_ORIGINAL
                , CON_BC_COMISSAO
                , PART_CODIGO
                , CONC_PERC_COMISSAO
                , PART_REPRESENTANTE_CODIGO
                , CON_ORIGEM
                , TIPO_BAIXA
            FROM(
                SELECT
                    REP.PART_NOME 							AS VENDEDOR
                    ,COALESCE(CLI.PART_NOME,FORN.PART_NOME)	AS CLIENTE
                    ,FIN_CONTAS.CON_CODIGO
                    ,FIN_CONTAS.CON_NUMERO
                    ,FIN_CONTAS.CON_SEQUENCIA
                    ,''                                     AS NR_CHEQUE
                    ,FIN_CONTAS.CON_DT_INCLUSAO
                    ,FIN_CONTAS.CON_DT_VENCIMENTO
                    ,CCP_DT_PAGAMENTO						AS DT_PAGAMENTO
                    ,CCP_VALOR								AS VLR_PAGO
                    ,CCP_JUROS                              AS JUROS
                    ,CON_VALOR_MULTA                        AS MULTA
                    ,COALESCE(CON_VALOR_OUTRASDESPESAS,0)   AS OUTRAS_DESPESAS
                    ,FIN_CONTAS.CON_VALOR_ORIGINAL
                    ,FIN_CONTAS.CON_BC_COMISSAO
                    ,FIN_CONTAS.PART_CODIGO
                    ,FIN_CONTAS_COMISSAO.CONC_PERC_COMISSAO
                    ,FIN_CONTAS_COMISSAO.PART_REPRESENTANTE_CODIGO
                    ,FIN_CONTAS.CON_ORIGEM
                    ,'DPL'									AS TIPO_BAIXA


                FROM FIN_CONTAS
                LEFT JOIN FIN_CONTAS_PAGAMENTOS ON FIN_CONTAS_PAGAMENTOS.CON_CODIGO = FIN_CONTAS.CON_CODIGO
                LEFT JOIN FIN_CONTAS_COMISSAO 	ON FIN_CONTAS_COMISSAO.CON_CODIGO 	= FIN_CONTAS.CON_CODIGO
                LEFT JOIN PARTICIPANTE AS REP 	ON  REP.PART_REPRESENTANTE_CODIGO 	= FIN_CONTAS_COMISSAO.PART_REPRESENTANTE_CODIGO
                LEFT JOIN PARTICIPANTE AS CLI 	ON CLI.PART_CLIENTE_CODIGO			= FIN_CONTAS.ENT_CODIGO
                LEFT JOIN PARTICIPANTE AS FORN 	ON FORN.PART_FORNECEDOR_CODIGO		= FIN_CONTAS.ENT_CODIGO
                LEFT JOIN FIN_CONTAS_CHEQUES 	ON FIN_CONTAS_CHEQUES.CCP_CODIGO	= FIN_CONTAS_PAGAMENTOS.CCP_CODIGO

                WHERE 1=1
                AND FIN_CONTAS.CON_TIPO = 0
                AND FIN_CONTAS_CHEQUES.CON_CODIGO IS NULL
                AND coalesce(FIN_CONTAS_PAGAMENTOS.DOC_NUMERO_ORIGEM,0)=0
                AND CCP_DT_PAGAMENTO > '1900-01-01'
                AND CON_ORIGEM NOT IN ('T')

                $data
                $cliente
                $vendedor
                $nf
                $parcela
                $rep_cod1

                UNION ALL
                    SELECT
                        REP.PART_NOME 							AS VENDEDOR
                        ,COALESCE(CLI.PART_NOME,FORN.PART_NOME)	AS CLIENTE
                        ,LIST(FIN_CONTAS.CON_CODIGO,',')		AS CON_CODIGO
                        ,'CH: '||CHQ_CHEQUE                     AS CON_NUMERO
                        ,1										AS CON_SEQUENCIA
                        ,LIST(CON_NUMERO||'-'||CON_SEQUENCIA,', ') AS NR_CHEQUE
                        ,CHEQUE.CHQ_DT_EMISSAO	                AS CON_DT_INCLUSAO
                        ,LIST(FIN_CONTAS.CON_DT_VENCIMENTO,',')	AS CON_DT_VENCIMENTO
                        ,CHEQUE.CHQ_BOM_PARA  					AS DT_PAGAMENTO
                        ,CHQ_VALOR        				        AS VLR_PAGO
                        ,0                                      AS JUROS
                        ,0                                      AS MULTA
                        ,0                                      AS OUTRAS_DESPESAS
                        ,CHQ_VALOR								AS CON_VALOR_ORIGINAL
                        ,CHQ_VALOR								AS CON_BC_COMISSAO
                        ,FIN_CONTAS.PART_CODIGO
                        ,FIN_CONTAS_COMISSAO.CONC_PERC_COMISSAO
                        ,FIN_CONTAS_COMISSAO.PART_REPRESENTANTE_CODIGO
                        ,'CHQ'				                    AS CON_ORIGEM
                        ,'CHQ'									AS TIPO_BAIXA


                    FROM CHEQUE
                    LEFT JOIN FIN_CONTAS_CHEQUES ON FIN_CONTAS_CHEQUES.CHQ_CODIGO = CHEQUE.CHQ_CODIGO
                    LEFT JOIN FIN_CONTAS ON FIN_CONTAS.CON_CODIGO = FIN_CONTAS_CHEQUES.CON_CODIGO
                    LEFT JOIN FIN_CONTAS_COMISSAO 	ON FIN_CONTAS_COMISSAO.CON_CODIGO 	= FIN_CONTAS.CON_CODIGO
                    LEFT JOIN PARTICIPANTE AS REP 	ON  REP.PART_REPRESENTANTE_CODIGO 	= FIN_CONTAS_COMISSAO.PART_REPRESENTANTE_CODIGO
                    LEFT JOIN PARTICIPANTE AS CLI 	ON CLI.PART_CLIENTE_CODIGO			= FIN_CONTAS.ENT_CODIGO
                    LEFT JOIN PARTICIPANTE AS FORN 	ON FORN.PART_FORNECEDOR_CODIGO		= FIN_CONTAS.ENT_CODIGO

                    WHERE 1=1
                    AND FIN_CONTAS.CON_TIPO = 0
                    AND CHQ_TERC = 0
                    AND FIN_CONTAS.CON_CODIGO IS NOT NULL
                    $dtCheq
                    $cliente
                    $vendedor
                    $nf
                    $parcela
                    $rep_cod1

                    GROUP BY
                        REP.PART_NOME
                        ,COALESCE(CLI.PART_NOME,FORN.PART_NOME)
                        ,'CH: '||CHQ_CHEQUE
                        ,CHQ_CHEQUE
                        ,CHEQUE.CHQ_BOM_PARA
                        ,CHQ_VALOR
                        ,CHQ_VALOR
                        ,CHQ_VALOR
                        ,FIN_CONTAS.PART_CODIGO
                        ,FIN_CONTAS_COMISSAO.CONC_PERC_COMISSAO
                        ,FIN_CONTAS_COMISSAO.PART_REPRESENTANTE_CODIGO
                        ,CHQ_DT_EMISSAO

            )COMISSAO
            ORDER BY
                VENDEDOR
                ,TIPO_BAIXA
                ,DT_PAGAMENTO
                ,CON_NUMERO
                ,CON_SEQUENCIA
        ";
        // dd($sql);
        $comissao = DB::connection(env('APP_NAME'))->select($sql);
        return $comissao;
    }
}
