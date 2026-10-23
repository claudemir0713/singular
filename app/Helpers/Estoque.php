<?php
namespace App\Helpers;

use App\Models\singular_estoque_bloco_k;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Estoque {
    public static function estoqueInicial($ano,$mes) {
        $dtInicial = date('Y-m-d',strtotime($ano.'-'.$mes.'-01')-1);
        $dtInicial = explode('-',$dtInicial);
        $ano =$dtInicial[0];
        $mes =$dtInicial[1];
        $estoqueInicial = [];
        $sql ="
            SELECT
                prd_codigo		AS COD_PROD
                , prd_descri    AS DESC_PROD
                , qtd			AS QTD
                , valor  		AS TOT
                , data			AS DT_FECHAMENTO
            FROM singular_estoque_bloco_k
            WHERE YEAR(data)    = $ano
            AND MONTH(data)	    = $mes
        ";
        $estoqueInicial = DB::connection('mysql')->select($sql);
        return $estoqueInicial;
    }
    public static function compra($ano,$mes) {
        $sql ="
            SELECT
                (PRODUTOS.PRD_CODIGO) 	                                AS COD_PROD
                ,PRODUTOS.PRD_DESCRICAO									AS DESC_PROD
                ,sum(COMPRAS_ITENS.CMI_QTDE_ENTRADA_ESTOQUE)			AS QTD
                ,sum(COMPRAS_ITENS.CMI_VL_TOTAL)						AS TOT
                ,COMPRAS_ITENS.cfop_codigo

            FROM COMPRAS

            LEFT JOIN COMPRAS_ITENS ON COMPRAS_ITENS.cmp_numero = COMPRAS.CMP_NUMERO
            LEFT JOIN PRODUTOS		ON PRODUTOS.PRD_CODIGO = COMPRAS_ITENS.PRD_CODIGO
            LEFT JOIN CFOP			ON CFOP.CFOP_CODIGO = COMPRAS_ITENS.cfop_codigo

            WHERE 1=1
            AND extract(year FROM COMPRAS.CMP_DT_ENTRADA) = $ano
            AND extract(month FROM COMPRAS.CMP_DT_ENTRADA)= $mes
            AND COMPRAS_ITENS.cfop_codigo IN ('1.101','1.102','1.122','1.407','1.501','1.551','1.556','1.653','1.901','1.912','1.916','1.922','1.949','2.101','2.102','2.122','2.252','2.556','2.653','2.924')
            AND COMPRAS.CMP_USU_COD_CONFIRMACAO IN ('3')
            AND PRD_TIPO_PRODUTO IN ('00','01','02','04')

            GROUP BY PRODUTOS.PRD_CODIGO, PRODUTOS.PRD_DESCRICAO, COMPRAS_ITENS.cfop_codigo
        ";
        $compra = DB::connection(env('APP_NAME'))->select($sql);
        return $compra;
    }
    public static function venda($ano,$mes) {
        $sql ="
            SELECT
                'VENDA'								AS TIPO
                ,PROD.PRD_CODIGO		  		    AS COD_PROD
                ,ITENS.NI_DESCRICAO                 AS DESC_PROD
                ,ITENS.NI_UNIMED					AS B1_CODUM
                ,TIPOS.DESCRICAO					AS B1_TIPO
                ,sum(ITENS.NI_QTDE)					AS QTD
                ,sum(NI_VL_TOTAL)					AS TOT

            FROM NF_VENDA_CABECALHO NF
            LEFT JOIN NF_VENDA_ITENS 	ITENS	ON ITENS.NF_NUMERO = NF.NF_NUMERO
            LEFT JOIN produtos 		 	PROD	ON PROD.PRD_CODIGO = ITENS.PRD_CODIGO
            LEFT JOIN PRODUTOS_TIPOS	TIPOS	ON TIPOS.CODIGO = PROD.PRD_TIPO_PRODUTO

            WHERE  1=1
            AND extract(year FROM NF.NF_DT_EMISSAO) = $ano
            AND extract(month FROM NF.NF_DT_EMISSAO) = $mes
            AND NF.CFOP_CODIGO IN  ('5.101','5.102','5.103','5.104','5.105','5.106','5.107','5.108','5.109','5.110','5.111','5.112','5.113','5.114','5.115','5.116','5.117','5.118','5.119','5.120','5.121','5.122','5.123','5.401','5.402','5.403','5.404','5.405','5.556','5.902','5.913','5.915','5.924','5.949','6.101','6.102','6.103','6.104','6.105','6.106','6.107','6.108','6.109','6.110','6.111','6.112','6.113','6.114','6.115','6.116','6.117','6.118','6.119','6.120','6.121','6.122','6.123','6.401','6.402','6.403','6.404','6.915','6.924','7.101')
            AND NF_SITUACAO = 1 --Emitida/Enviada
            --AND PRD_TIPO_PRODUTO IN ('04')

            GROUP BY 'VENDA'
                    ,PROD.PRD_CODIGO
                    ,ITENS.NI_DESCRICAO
                    ,ITENS.NI_UNIMED
                    ,TIPOS.DESCRICAO
        ";
        $venda = DB::connection(env('APP_NAME'))->select($sql);
        return $venda;
    }

    public static function consumo($ano,$mes) {
        $sql_venda = "
            SELECT
                PROD.PRD_CODIGO		  			AS COD_PROD
                ,PRD_TIPO_PRODUTO
                ,sum(ITENS.NI_QTDE)             AS QTD_PROD
            FROM NF_VENDA_CABECALHO NF
            LEFT JOIN NF_VENDA_ITENS 	ITENS	ON ITENS.NF_NUMERO = NF.NF_NUMERO
            LEFT JOIN produtos 		 	PROD	ON PROD.PRD_CODIGO = ITENS.PRD_CODIGO
            LEFT JOIN PRODUTOS_TIPOS	TIPOS	ON TIPOS.CODIGO = PROD.PRD_TIPO_PRODUTO

            WHERE  1=1
            AND extract(year FROM NF.NF_DT_EMISSAO) = $ano
            AND extract(month FROM NF.NF_DT_EMISSAO) = $mes
            AND NF.CFOP_CODIGO IN  ('5.101','5.102','5.103','5.104','5.105','5.106','5.107','5.108','5.109','5.110','5.111','5.112','5.113','5.114','5.115','5.116','5.117','5.118','5.119','5.120','5.121','5.122','5.123','5.401','5.402','5.403','5.404','5.405','5.556','5.902','5.913','5.915','5.924','5.949','6.101','6.102','6.103','6.104','6.105','6.106','6.107','6.108','6.109','6.110','6.111','6.112','6.113','6.114','6.115','6.116','6.117','6.118','6.119','6.120','6.121','6.122','6.123','6.401','6.402','6.403','6.404','6.915','6.924','7.101')
            AND NF_SITUACAO = 1 --Emitida/Enviada
            --AND PRD_TIPO_PRODUTO IN ('04')

            GROUP BY PROD.PRD_CODIGO,PRD_TIPO_PRODUTO
        ";
        $ProdutoVenda = DB::connection(env('APP_NAME'))->select($sql_venda);
        $ProCod     = '';
        $QTD_PROD   = 0;
        foreach($ProdutoVenda as $item){
            $ProCod     .="'".$item->COD_PROD."',";
            $QTD_PROD   += $item->QTD_PROD;
        }
        if($QTD_PROD==0){$QTD_PROD=1;};
        $QTD_PROD=1;
        $ProCod = rtrim($ProCod,",");
        $sql ="
            SELECT
                'CONSUMO'			  						AS TIPO
                ,RTRIM(LTRIM(REPLACE(COD_COMP,CHAR(9),''))) AS COD_PROD
                ,DESC_COMP									AS DESC_PROD
                ,B1_CODUM
                ,TIPO_COMP
                ,SUM(COALESCE(QTD,0)+COALESCE(PERDA,0))*$QTD_PROD AS QTD

            FROM V_ESTRUTURA31
            LEFT JOIN SB1 ON LEFT(SB1.B1_FILIAL,2) = LEFT(V_ESTRUTURA31.FILIAL,2)
                        AND SB1.B1_CODPROD = V_ESTRUTURA31.COD_COMP

            WHERE TIPO_COMP NOT IN (6)
            AND V_ESTRUTURA31.CODIGO IN ($ProCod)
            GROUP BY COD_COMP
                    ,DESC_COMP
                    ,B1_CODUM
                    ,B1_TIPO
                    ,TIPO_COMP
        ";
        $consumo = DB::connection('totvs')->select($sql);
        return $consumo;
    }
    public static function producao($ano,$mes) {
        $sql ="
            SELECT
                'VENDA'								AS TIPO
                ,PROD.PRD_CODIGO		  		    AS COD_PROD
                ,ITENS.NI_DESCRICAO                 AS DESC_PROD
                ,ITENS.NI_UNIMED					AS B1_CODUM
                ,TIPOS.DESCRICAO					AS B1_TIPO
                ,sum(ITENS.NI_QTDE)					AS QTD
                ,sum(NI_VL_TOTAL)					AS TOT

            FROM NF_VENDA_CABECALHO NF
            LEFT JOIN NF_VENDA_ITENS 	ITENS	ON ITENS.NF_NUMERO = NF.NF_NUMERO
            LEFT JOIN produtos 		 	PROD	ON PROD.PRD_CODIGO = ITENS.PRD_CODIGO
            LEFT JOIN PRODUTOS_TIPOS	TIPOS	ON TIPOS.CODIGO = PROD.PRD_TIPO_PRODUTO

            WHERE  1=1
            AND extract(year FROM NF.NF_DT_EMISSAO) = $ano
            AND extract(month FROM NF.NF_DT_EMISSAO) = $mes
            AND NF.CFOP_CODIGO IN  ('5.101','5.102','5.103','5.104','5.105','5.106','5.107','5.108','5.109','5.110','5.111','5.112','5.113','5.114','5.115','5.116','5.117','5.118','5.119','5.120','5.121','5.122','5.123','5.401','5.402','5.403','5.404','5.405','7.101','6.101','6.102','6.103','6.104','6.105','6.106','6.107','6.108','6.109','6.110','6.111','6.112','6.113','6.114','6.115','6.116','6.117','6.118','6.119','6.120','6.121','6.122','6.123','6.401','6.402','6.403','6.404')
            AND NF_SITUACAO = 1 --Emitida/Enviada
            AND PRD_TIPO_PRODUTO IN ('04')

            GROUP BY 'VENDA'
                    ,PROD.PRD_CODIGO
                    ,ITENS.NI_DESCRICAO
                    ,ITENS.NI_UNIMED
                    ,TIPOS.DESCRICAO
        ";
        $producao = DB::connection(env('APP_NAME'))->select($sql);
        return $producao;
    }


    public static function estoqueFinal($ano,$mes) {
        $estoqueFinal = [];
        $estoqueInicial =  Estoque::estoqueInicial($ano,$mes);
        $compra         =  Estoque::compra($ano,$mes);
        $venda          =  Estoque::venda($ano,$mes);
        $consumo        =  Estoque::consumo($ano,$mes);
        $producao       =  Estoque::producao($ano,$mes);

        foreach($estoqueInicial as $item){
            $estoqueFinal[] = [
                'COD_PROD'  => $item->COD_PROD
                ,'DESC_PROD'        =>$item->DESC_PROD
                ,'QTD_INICIAL'      =>$item->QTD
                ,'TOT_INICIAL'      =>$item->TOT
                ,'QTD_COMPRA'       =>0
                ,'TOT_COMPRA'       =>0
                ,'QTD_CONSUMO'      =>0
                ,'TOT_CONSUMO'      =>0
                ,'QTD_PRODUCAO'     =>0
                ,'TOT_PRODUCAO'     =>0
                ,'QTD_VENDA'        =>0
                ,'TOT_VENDA'        =>0
            ];
        }
        foreach($compra as $item){
            $estoqueFinal[] = [
                'COD_PROD'  => $item->COD_PROD
                ,'DESC_PROD'        =>$item->DESC_PROD
                ,'QTD_INICIAL'      =>0
                ,'TOT_INICIAL'      =>0
                ,'QTD_COMPRA'       =>$item->QTD
                ,'TOT_COMPRA'       =>$item->TOT
                ,'QTD_CONSUMO'      =>0
                ,'TOT_CONSUMO'      =>0
                ,'QTD_PRODUCAO'     =>0
                ,'TOT_PRODUCAO'     =>0
                ,'QTD_VENDA'        =>0
                ,'TOT_VENDA'        =>0
            ];
        }
        foreach($venda as $item){
            $estoqueFinal[] = [
                'COD_PROD'  => $item->COD_PROD
                ,'DESC_PROD'        =>$item->DESC_PROD
                ,'QTD_INICIAL'      =>0
                ,'TOT_INICIAL'      =>0
                ,'QTD_COMPRA'       =>0
                ,'TOT_COMPRA'       =>0
                ,'QTD_CONSUMO'      =>0
                ,'TOT_CONSUMO'      =>0
                ,'QTD_PRODUCAO'     =>0
                ,'TOT_PRODUCAO'     =>0
                ,'QTD_VENDA'        =>$item->QTD
                ,'TOT_VENDA'        =>$item->TOT
            ];
        }
        foreach($consumo as $item){
            $estoqueFinal[] = [
                'COD_PROD'  => $item->COD_PROD
                ,'DESC_PROD'        =>$item->DESC_PROD
                ,'QTD_INICIAL'      =>0
                ,'TOT_INICIAL'      =>0
                ,'QTD_COMPRA'       =>0
                ,'TOT_COMPRA'       =>0
                ,'QTD_CONSUMO'      =>$item->QTD
                ,'TOT_CONSUMO'      =>0
                ,'QTD_PRODUCAO'     =>0
                ,'TOT_PRODUCAO'     =>0
                ,'QTD_VENDA'        =>0
                ,'TOT_VENDA'        =>0
            ];
        }
        foreach($consumo as $item){
            if($item->TIPO_COMP==3 || $item->TIPO_COMP==2){
                $estoqueFinal[] = [
                    'COD_PROD'  => $item->COD_PROD
                    ,'DESC_PROD'        =>$item->DESC_PROD
                    ,'QTD_INICIAL'      =>0
                    ,'TOT_INICIAL'      =>0
                    ,'QTD_COMPRA'       =>0
                    ,'TOT_COMPRA'       =>0
                    ,'QTD_CONSUMO'      =>0
                    ,'TOT_CONSUMO'      =>0
                    ,'QTD_PRODUCAO'     =>$item->QTD
                    ,'TOT_PRODUCAO'     =>0
                    ,'QTD_VENDA'        =>0
                    ,'TOT_VENDA'        =>0
                ];
            }
        }
        foreach($producao as $item){
            $estoqueFinal[] = [
                'COD_PROD'  => $item->COD_PROD
                ,'DESC_PROD'        =>$item->DESC_PROD
                ,'QTD_INICIAL'      =>0
                ,'TOT_INICIAL'      =>0
                ,'QTD_COMPRA'       =>0
                ,'TOT_COMPRA'       =>0
                ,'QTD_CONSUMO'      =>0
                ,'TOT_CONSUMO'      =>0
                ,'QTD_PRODUCAO'     =>$item->QTD
                ,'TOT_PRODUCAO'     =>$item->TOT
                ,'QTD_VENDA'        =>0
                ,'TOT_VENDA'        =>0
            ];
        }


        /*****************agrupando e somando por produto******************/
        $estoqueRtorno = array();
        foreach ($estoqueFinal as $item) {
            $COD_PROD = $item['COD_PROD'];
            if (!isset($estoqueRtorno[$COD_PROD])) {
            $estoqueRtorno[$COD_PROD] = array(
                'COD_PROD'       => $item['COD_PROD']
                ,'DESC_PROD'     => $item['DESC_PROD']
                ,'QTD_INICIAL'   => 0
                ,'TOT_INICIAL'   => 0
                ,'QTD_COMPRA'    => 0
                ,'TOT_COMPRA'    => 0
                ,'QTD_CONSUMO'   => 0
                ,'TOT_CONSUMO'   => 0
                ,'QTD_PRODUCAO'  => 0
                ,'TOT_PRODUCAO'  => 0
                ,'QTD_VENDA'     => 0
                ,'TOT_VENDA'     => 0
                );
            }
            $estoqueRtorno[$COD_PROD]['QTD_INICIAL']  += $item['QTD_INICIAL'];
            $estoqueRtorno[$COD_PROD]['TOT_INICIAL']  += $item['TOT_INICIAL'];
            $estoqueRtorno[$COD_PROD]['QTD_COMPRA']   += $item['QTD_COMPRA'];
            $estoqueRtorno[$COD_PROD]['TOT_COMPRA']   += $item['TOT_COMPRA'];
            $estoqueRtorno[$COD_PROD]['QTD_CONSUMO']  += $item['QTD_CONSUMO'];
            $estoqueRtorno[$COD_PROD]['TOT_CONSUMO']  += $item['TOT_CONSUMO'];
            $estoqueRtorno[$COD_PROD]['QTD_PRODUCAO'] += $item['QTD_PRODUCAO'];
            $estoqueRtorno[$COD_PROD]['TOT_PRODUCAO'] += $item['TOT_PRODUCAO'];
            $estoqueRtorno[$COD_PROD]['QTD_VENDA']    += $item['QTD_VENDA'];
            $estoqueRtorno[$COD_PROD]['TOT_VENDA']    += $item['TOT_VENDA'];
        }

        /****************pegando ano me mes passados e gerando o ultimo dia**************/
        $data_fechamento = $ano.'-'.$mes.'-01';
        $data_fechamento = date("Y-m-t", strtotime($data_fechamento));
        $existeFechamento = singular_estoque_bloco_k::where('data',$data_fechamento)->count(); /*verificando se existe fechamento na data*/
        if($existeFechamento>0){/*se encontrar elimina*/
            singular_estoque_bloco_k::where('data',$data_fechamento)->delete();
        };
        /******************gravando o proximo fechamento**********************************/
        print_r('COD_PROD|DESC_PROD|QTD_INICIAL|QTD_COMPRA|QTD_CONSUMO|QTD_PRODUCAO|QTD_VENDA|TOT_INICIAL|TOT_COMPRA|TOT_CONSUMO|TOT_PRODUCAO|TOT_VENDA|data_fechamento<br>');
        foreach($estoqueRtorno as $item){
            $UNT_CONSUMO = 1;
            if( $item['QTD_COMPRA']>0 && $item['TOT_COMPRA']>0 ){
                $UNT_CONSUMO = $item['TOT_COMPRA']/$item['QTD_COMPRA'];
            }elseif($item['QTD_INICIAL']>0  && $item['TOT_INICIAL']>0){
                $UNT_CONSUMO = $item['TOT_INICIAL']/$item['QTD_INICIAL'];
            }
            $QTD_CONSUMO = $item['QTD_CONSUMO'];
            $TOT_CONSUMO = $QTD_CONSUMO * $UNT_CONSUMO;
            $TOT_PRODUCAO = $item['QTD_PRODUCAO'] * $UNT_CONSUMO;

            $qtd    = $item['QTD_INICIAL'] + $item['QTD_COMPRA'] - $QTD_CONSUMO + $item['QTD_PRODUCAO'] - $item['QTD_VENDA'];
            $valor  = $item['TOT_INICIAL'] + $item['TOT_COMPRA'] - $TOT_CONSUMO + $TOT_PRODUCAO         - $item['TOT_VENDA'];
            $fechamento = new singular_estoque_bloco_k([
                'prd_codigo'    => $item['COD_PROD']
                , 'prd_descri'  => $item['DESC_PROD']
                , 'qtd'         => $qtd
                , 'valor'       => $valor
                , 'data'        => $data_fechamento
            ]);
            $fechamento->save();
            print_r($item['COD_PROD'].'|'.$item['DESC_PROD'].'|'.number_format($item['QTD_INICIAL'],4,',','.').'|'.number_format($item['QTD_COMPRA'],4,',','.').'|'.number_format($QTD_CONSUMO,4,',','.').'|'.number_format($item['QTD_PRODUCAO'],4,',','.').'|'.number_format($item['QTD_VENDA'],4,',','.').'|'.number_format($item['TOT_INICIAL'],4,',','.').'|'.number_format($item['TOT_COMPRA'],4,',','.').'|'.number_format($TOT_CONSUMO,4,',','.').'|'.number_format($item['TOT_PRODUCAO'],4,',','.').'|'.number_format($item['TOT_VENDA'],4,',','.').'|'.$data_fechamento.'<br>');
        }
        return ($estoqueRtorno);
    }
}
