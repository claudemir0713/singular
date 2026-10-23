<?php
namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Vendas {
    public static function vendas($dataI,$dataF,$cliente,$vendedor,$nf,$nivel,$rep_cod) {
        $rep_cod1 = '';
        if($nivel=='restrito'){
            $rep_cod1 = " AND REP.PART_CODIGO  = $rep_cod";
        }
        $cliente    = " AND CLIENTE LIKE '$cliente%'";
        $vendedor   = " AND VENDEDOR LIKE '%$vendedor%'";
        $data_nf    = " AND NF.NF_DT_EMISSAO BETWEEN '$dataI' AND '$dataF'";
        $data_dav   = " AND DAV_DATA_EMISSAO BETWEEN '$dataI' AND '$dataF'";
        if($nf){
            $nf = preg_replace('/[^0-9]/', '', $nf);
            $cliente    = '';
            $vendedor   = '';
            $data_nf    = '';
            $data_dav   = '';
            $docnf      = " AND NF.NF_NUMERO_REAL = '$nf'";
            $docdav     = " AND DAV.DAV_NUMERO_AUXILIAR = '$nf'";
        }else{
            $docnf      = '';
            $docdav     = '';
        }
        $sql = "
            SELECT
                ORIGEM
                , TIPO
                , TIPO_RECEITA
                , EMPRESA
                , DOCUMENTO
                , SERIE_FAT
                , DATA_COMP
                , NUM_PED
                , DATA_PED
                , COD_CLIENTE
                , CLIENTE
                , CEP_IBGE
                , MUNICIPIO
                , ESTADO
                , PAIS
                , COD_VENDEDOR
                , VENDEDOR
                , FORMA_DE_PAGTO
                , SUM(QUANTIDADE)		AS QUANTIDADE
                , SUM(FAT_BRUTO)		AS FAT_BRUTO
                , SUM(FAT_BRUTO_2)		AS FAT_BRUTO_2
                , SUM(DESCONTO)			AS DESCONTO
                , SUM(ACRESCIMO)		AS ACRESCIMO
                , SUM(FAT_LIQUIDO)		AS FAT_LIQUIDO
                , FRETE_DAV	            AS FRETE
                , SUM(IPI)              AS IPI
            FROM(
                /******************VENDA COM DIFERENÇA NO VALOR***************************************/
                    SELECT
                        'SISTEMA' 									AS ORIGEM
                        ,'VENDA' 									AS TIPO
                        ,'NF' 								        AS TIPO_RECEITA
                        ,'DECORBRAS' 			   					AS EMPRESA
                        ,NF.NF_NUMERO_REAL	   		   				AS DOCUMENTO
                        ,NF.NS_SERIE			   					AS SERIE_FAT
                        ,NF.NF_DT_EMISSAO 		   	 				AS DATA_COMP
                        ,DAV.DAV_NUMERO_AUXILIAR					AS NUM_PED
                        ,DAV.DAV_DATA		   						AS DATA_PED
                        ,NF.ENT_CODIGO 				   				AS COD_CLIENTE
                        ,NF.ENT_NOME 			   	   				AS CLIENTE
                        ,NF.NF_EMISSAO_CEP         					AS CEP_IBGE
                        ,NF_EMISSAO_CIDADE		   	   				AS MUNICIPIO
                        ,NF_EMISSAO_UF			   	   				AS ESTADO
                        ,PAIS.PAIS_NOME				  				AS PAIS
                        ,NF.PART_REPRESENTANTE_CODIGO  				AS COD_VENDEDOR
                        ,REP.PART_NOME								AS VENDEDOR
                        ,COND_PGTO.CPA_DESCRICAO					AS FORMA_DE_PAGTO
                        ,ITENS.PRD_CODIGO							AS COD_PRODUTO
                        ,ITENS.NI_DESCRICAO			  	 			AS PRODUTO
                        ,NI_UNIMED 									AS UN_MEDIDA
                        ,ITENS.NI_QTDE								AS QUANTIDADE
                        ,NI_VL_UNITARIO
                        ,DAVI_VL_UNIT
                        ,ITENS.NI_VL_TOTAL 				 			AS FAT_BRUTO
                        ,CASE
                            WHEN ((DAVI_VL_TOTAL/DAVI_QTDE)-NI_VL_UNITARIO) >0 THEN ((DAVI_VL_TOTAL/DAVI_QTDE)-NI_VL_UNITARIO)  * NI_QTDE
                            ELSE 0
                        END											AS FAT_BRUTO_2
                        ,ITENS.NI_VL_DESCONTO			 			AS DESCONTO
                        , 0 										AS ACRESCIMO
                        ,ITENS.NI_VL_TOTAL-ITENS.NI_VL_DESCONTO  	AS FAT_LIQUIDO
                        ,ITENS.NI_VL_IPI				 			AS IPI
                        ,ITENS.NI_VL_COFINS							AS COFINS
                        ,ITENS.NI_VL_ICMS               			AS ICMS
                        ,NF.NF_VL_CSLL								AS CSLL
                        ,0											AS IRPJ
                        ,NF.NF_VL_ISS 								AS ISS
                        ,0 											AS PERC_COMISSAO
                        ,ITENS.NI_COMISSAO              			AS COMISSAO
                        ,NF.NF_VL_FRETE								AS FRETE
                        ,DAV.DAV_VALOR_FRETE                        AS FRETE_DAV

                    FROM NF_VENDA_CABECALHO NF
                    LEFT JOIN NF_VENDA_ITENS ITENS ON ITENS.NF_NUMERO = NF.NF_NUMERO
                    LEFT JOIN CFOP ON CFOP.CFOP_CODIGO_NF = ITENS.CFOP_CODIGO_NF
                    LEFT JOIN CFOP_GRUPOS ON CFOP_GRUPOS.CFGR_CODIGO = CFOP.CFGR_CODIGO

                    LEFT JOIN dav ON dav.DAV_NUMERO = ITENS.PED_NUMERO
                    LEFT JOIN dav_itens ON dav_itens.DAV_NUMERO = dav.DAV_NUMERO
                                        AND dav_itens.DAVI_ITEM = ITENS.DAVI_ITEM_IMPORTADO

                    LEFT JOIN PAIS ON PAIS.PAIS_CODIGO = NF.PAIS_CODIGO
                    LEFT JOIN PARTICIPANTE AS REP ON REP.PART_REPRESENTANTE_CODIGO = NF.PART_REPRESENTANTE_CODIGO

                    LEFT JOIN CONDICOES_PAGAMENTO COND_PGTO ON COND_PGTO.CPA_CODIGO = NF.CON_TIPO_PAGAMENTO

                    WHERE 1=1
                    AND (CFOP_GRUPOS.CFGR_CODIGO = 1 OR RIGHT(trim(ITENS.CFOP_CODIGO),3) = '124')
                    AND NF_SITUACAO IN(1)
                    AND dav.DAV_NUMERO IS NOT NULL
                    AND coalesce(DAV.DAV_SITUACAO_ENTREGA,0) <> 1
                    $data_nf
                    $docnf
                    $rep_cod1

                /******************VENDA SEM PEDIDO***************************************/
                    UNION ALL
                    SELECT
                        'SISTEMA' 									AS ORIGEM
                        ,'VENDA' 									AS TIPO
                        ,'NF' 								        AS TIPO_RECEITA
                        ,'DECORBRAS' 			   					AS EMPRESA
                        ,NF.NF_NUMERO_REAL	   		   				AS DOCUMENTO
                        ,NF.NS_SERIE			   					AS SERIE_FAT
                        ,NF.NF_DT_EMISSAO 		   	 				AS DATA_COMP
                        ,0					                        AS NUM_PED
                        ,''		   						            AS DATA_PED
                        ,NF.ENT_CODIGO 				   				AS COD_CLIENTE
                        ,NF.ENT_NOME 			   	   				AS CLIENTE
                        ,NF.NF_EMISSAO_CEP         					AS CEP_IBGE
                        ,NF_EMISSAO_CIDADE		   	   				AS MUNICIPIO
                        ,NF_EMISSAO_UF			   	   				AS ESTADO
                        ,PAIS.PAIS_NOME				  				AS PAIS
                        ,NF.PART_REPRESENTANTE_CODIGO  				AS COD_VENDEDOR
                        ,REP.PART_NOME								AS VENDEDOR
                        ,COND_PGTO.CPA_DESCRICAO					AS FORMA_DE_PAGTO
                        ,ITENS.PRD_CODIGO							AS COD_PRODUTO
                        ,ITENS.NI_DESCRICAO			  	 			AS PRODUTO
                        ,NI_UNIMED 									AS UN_MEDIDA
                        ,ITENS.NI_QTDE								AS QUANTIDADE
                        ,NI_VL_UNITARIO
                        ,0
                        ,ITENS.NI_VL_TOTAL 				 			AS FAT_BRUTO
                        ,0											AS FAT_BRUTO_2
                        ,ITENS.NI_VL_DESCONTO			 			AS DESCONTO
                        , 0 										AS ACRESCIMO
                        ,ITENS.NI_VL_TOTAL-ITENS.NI_VL_DESCONTO  	AS FAT_LIQUIDO
                        ,ITENS.NI_VL_IPI				 			AS IPI
                        ,ITENS.NI_VL_COFINS							AS COFINS
                        ,ITENS.NI_VL_ICMS               			AS ICMS
                        ,NF.NF_VL_CSLL								AS CSLL
                        ,0											AS IRPJ
                        ,NF.NF_VL_ISS 								AS ISS
                        ,0 											AS PERC_COMISSAO
                        ,ITENS.NI_COMISSAO              			AS COMISSAO
                        ,NF.NF_VL_FRETE								AS FRETE
                        ,0                                          AS FRETE_DAV

                    FROM NF_VENDA_CABECALHO NF
                    LEFT JOIN NF_VENDA_ITENS ITENS ON ITENS.NF_NUMERO = NF.NF_NUMERO
                    LEFT JOIN CFOP ON CFOP.CFOP_CODIGO_NF = ITENS.CFOP_CODIGO_NF
                    LEFT JOIN CFOP_GRUPOS ON CFOP_GRUPOS.CFGR_CODIGO = CFOP.CFGR_CODIGO

                    LEFT JOIN dav ON dav.DAV_NUMERO = ITENS.PED_NUMERO
                    LEFT JOIN dav_itens ON dav_itens.DAV_NUMERO = dav.DAV_NUMERO
                                        AND dav_itens.DAVI_ITEM = ITENS.DAVI_ITEM_IMPORTADO

                    LEFT JOIN PAIS ON PAIS.PAIS_CODIGO = NF.PAIS_CODIGO
                    LEFT JOIN PARTICIPANTE AS REP ON REP.PART_REPRESENTANTE_CODIGO = NF.PART_REPRESENTANTE_CODIGO

                    LEFT JOIN CONDICOES_PAGAMENTO COND_PGTO ON COND_PGTO.CPA_CODIGO = NF.CON_TIPO_PAGAMENTO

                    WHERE 1=1
                    AND (CFOP_GRUPOS.CFGR_CODIGO = 1 OR RIGHT(trim(ITENS.CFOP_CODIGO),3) = '124')
                    AND NF_SITUACAO IN(1)
                    AND dav.DAV_NUMERO IS NULL
                    AND coalesce(DAV.DAV_SITUACAO_ENTREGA,0) <> 1
                    $data_nf
                    $docnf
                    $rep_cod1

                /*******************VENDA COM DIFERENCA NA QUANTIDADE*****************************************************/
                    UNION ALL
                    SELECT
                        'SISTEMA' 									AS ORIGEM
                        ,'VENDA' 									AS TIPO
                        ,'NF' 								        AS TIPO_RECEITA
                        ,'DECORBRAS' 			   					AS EMPRESA
                        ,NF.NF_NUMERO_REAL	   		   				AS DOCUMENTO
                        ,NF.NS_SERIE			   					AS SERIE_FAT
                        ,NF.NF_DT_EMISSAO 		   	 				AS DATA_COMP
                        ,DAV.DAV_NUMERO_AUXILIAR					AS NUM_PED
                        ,DAV.DAV_DATA		   						AS DATA_PED
                        ,NF.ENT_CODIGO 				   				AS COD_CLIENTE
                        ,NF.ENT_NOME 			   	   				AS CLIENTE
                        ,NF.NF_EMISSAO_CEP         					AS CEP_IBGE
                        ,NF_EMISSAO_CIDADE		   	   				AS MUNICIPIO
                        ,NF_EMISSAO_UF			   	   				AS ESTADO
                        ,PAIS.PAIS_NOME				  				AS PAIS
                        ,NF.PART_REPRESENTANTE_CODIGO  				AS COD_VENDEDOR
                        ,REP.PART_NOME								AS VENDEDOR
                        ,COND_PGTO.CPA_DESCRICAO					AS FORMA_DE_PAGTO
                        ,ITENS.PRD_CODIGO							AS COD_PRODUTO
                        ,ITENS.NI_DESCRICAO			  	 			AS PRODUTO
                        ,NI_UNIMED 									AS UN_MEDIDA
                        ,DAVI_QTDE									AS QUANTIDADE
                        ,NI_VL_UNITARIO
                        ,DAVI_VL_UNIT
                        ,ITENS.NI_VL_TOTAL 				 			AS FAT_BRUTO
                        ,(DAVI_QTDE - ITENS.NI_QTDE)*DAVI_VL_UNIT   AS FAT_BRUTO_2
                        ,ITENS.NI_VL_DESCONTO			 			AS DESCONTO
                        , 0 										AS ACRESCIMO
                        ,ITENS.NI_VL_TOTAL-ITENS.NI_VL_DESCONTO  	AS FAT_LIQUIDO
                        ,ITENS.NI_VL_IPI				 			AS IPI
                        ,ITENS.NI_VL_COFINS							AS COFINS
                        ,ITENS.NI_VL_ICMS               			AS ICMS
                        ,NF.NF_VL_CSLL								AS CSLL
                        ,0											AS IRPJ
                        ,NF.NF_VL_ISS 								AS ISS
                        ,0 											AS PERC_COMISSAO
                        ,ITENS.NI_COMISSAO              			AS COMISSAO
                        ,NF.NF_VL_FRETE								AS FRETE
                        ,DAV.DAV_VALOR_FRETE                        AS FRETE_DAV

                    FROM NF_VENDA_CABECALHO NF
                    LEFT JOIN NF_VENDA_ITENS ITENS ON ITENS.NF_NUMERO = NF.NF_NUMERO
                    LEFT JOIN CFOP ON CFOP.CFOP_CODIGO_NF = ITENS.CFOP_CODIGO_NF
                    LEFT JOIN CFOP_GRUPOS ON CFOP_GRUPOS.CFGR_CODIGO = CFOP.CFGR_CODIGO

                    LEFT JOIN dav ON dav.DAV_NUMERO = ITENS.PED_NUMERO
                    LEFT JOIN dav_itens ON dav_itens.DAV_NUMERO = dav.DAV_NUMERO
                                        AND dav_itens.DAVI_ITEM = ITENS.DAVI_ITEM_IMPORTADO

                    LEFT JOIN PAIS ON PAIS.PAIS_CODIGO = NF.PAIS_CODIGO
                    LEFT JOIN PARTICIPANTE AS REP ON REP.PART_REPRESENTANTE_CODIGO = NF.PART_REPRESENTANTE_CODIGO

                    LEFT JOIN CONDICOES_PAGAMENTO COND_PGTO ON COND_PGTO.CPA_CODIGO = NF.CON_TIPO_PAGAMENTO

                    WHERE 1=1
                    AND (CFOP_GRUPOS.CFGR_CODIGO = 1 OR RIGHT(trim(ITENS.CFOP_CODIGO),3) = '124')
                    AND NF_SITUACAO IN(1)
                    AND dav.DAV_NUMERO IS NOT NULL
                    AND coalesce(DAV.DAV_SITUACAO_ENTREGA,1) =  1
                    $data_nf
                    $docnf
                    $rep_cod1

                    UNION ALL
                    SELECT
                        'SISTEMA'          								AS ORIGEM
                        ,'VENDA'          								AS TIPO
                        ,'NF'                 						    AS TIPO_RECEITA
                        ,'DECORBRAS'            						AS EMPRESA
                        ,NF_NUMERO_REAL   								AS DOCUMENTO
                        ,NS_SERIE           							AS SERIE_FAT
                        ,NF_DT_EMISSAO           						AS DATA_COMP
                        ,DAV.DAV_NUMERO_AUXILIAR    					AS NUM_PED
                        ,DAV.DAV_DATA           						AS DATA_PED
                        ,DADOS_NOTA.ENT_CODIGO            				AS COD_CLIENTE
                        ,DADOS_NOTA.ENT_NOME               				AS CLIENTE
                        ,NF_EMISSAO_CEP          						AS CEP_IBGE
                        ,NF_EMISSAO_CIDADE          					AS MUNICIPIO
                        ,NF_EMISSAO_UF              					AS ESTADO
                        ,PAIS_NOME          							AS PAIS
                        ,DADOS_NOTA.PART_REPRESENTANTE_CODIGO   		AS COD_VENDEDOR
                        ,DADOS_NOTA.PART_NOME        					AS VENDEDOR
                        ,CPA_DESCRICAO     								AS FORMA_DE_PAGTO
                        ,DAV_ITENS.PRD_CODIGO       					AS COD_PRODUTO
                        ,DAVI_DESCRICAO          						AS PRODUTO
                        ,DAVI_UM          								AS UN_MEDIDA
                        ,DAVI_QTDE         								AS QUANTIDADE
                        ,DAVI_VL_UNIT									AS NI_VL_UNITARIO
                        ,DAVI_VL_UNIT
                        ,0         										AS FAT_BRUTO
                        ,DAVI_QTDE*DAVI_VL_UNIT 						AS FAT_BRUTO_2
                        ,0       										AS DESCONTO
                        , 0           									AS ACRESCIMO
                        ,DAVI_QTDE*DAVI_VL_UNIT   						AS FAT_LIQUIDO
                        ,0				        						AS IPI
                        ,0					       						AS COFINS
                        ,0				                  				AS ICMS
                        ,0			        							AS CSLL
                        ,0           									AS IRPJ
                        ,0			         							AS ISS
                        ,0            									AS PERC_COMISSAO
                        ,DAVI_COMISSAO_PERC                 			AS COMISSAO
                        ,0        										AS FRETE
                        ,DAV.DAV_VALOR_FRETE                        	AS FRETE_DAV
                    FROM(
                        SELECT
                            DAV.DAV_NUMERO
                            ,NF.NF_NUMERO
                            ,NF.NF_NUMERO_REAL
                            ,NF.NS_SERIE
                            ,NF.NF_DT_EMISSAO
                            ,NF.NF_EMISSAO_CEP
                            ,NF.NF_EMISSAO_CIDADE
                            ,NF.NF_EMISSAO_UF
                            ,NF.ENT_CODIGO
                            ,NF.ENT_NOME
                            ,PAIS.PAIS_NOME
                            ,NF.PART_REPRESENTANTE_CODIGO
                            ,REP.PART_NOME
                            ,COND_PGTO.CPA_DESCRICAO

                        FROM NF_VENDA_CABECALHO NF
                        LEFT JOIN NF_VENDA_ITENS ITENS ON ITENS.NF_NUMERO = NF.NF_NUMERO
                        LEFT JOIN CFOP ON CFOP.CFOP_CODIGO_NF = ITENS.CFOP_CODIGO_NF
                        LEFT JOIN CFOP_GRUPOS ON CFOP_GRUPOS.CFGR_CODIGO = CFOP.CFGR_CODIGO

                        LEFT JOIN DAV ON DAV.DAV_NUMERO = ITENS.PED_NUMERO
                        LEFT JOIN DAV_ITENS ON DAV_ITENS.DAV_NUMERO = dav.DAV_NUMERO
                                            AND DAV_ITENS.DAVI_ITEM = ITENS.DAVI_ITEM_IMPORTADO
                        LEFT JOIN PAIS ON PAIS.PAIS_CODIGO = NF.PAIS_CODIGO
                        LEFT JOIN PARTICIPANTE AS REP ON REP.PART_REPRESENTANTE_CODIGO = NF.PART_REPRESENTANTE_CODIGO

                        LEFT JOIN CONDICOES_PAGAMENTO COND_PGTO ON COND_PGTO.CPA_CODIGO = NF.CON_TIPO_PAGAMENTO

                        WHERE 1=1
                        AND (CFOP_GRUPOS.CFGR_CODIGO = 1 OR RIGHT(trim(ITENS.CFOP_CODIGO),3) = '124')
                        AND NF_SITUACAO IN(1)
                        AND coalesce(DAV.DAV_SITUACAO_ENTREGA,1) =  1
                        AND dav.DAV_NUMERO IS NOT NULL
                        $data_nf
                        $docnf
                        $rep_cod1
                        GROUP BY
                            DAV.DAV_NUMERO
                            ,NF.NF_NUMERO
                            ,NF.NF_NUMERO_REAL
                            ,NF.NS_SERIE
                            ,NF.NF_DT_EMISSAO
                            ,NF.NF_EMISSAO_CEP
                            ,NF.NF_EMISSAO_CIDADE
                            ,NF_EMISSAO_UF
                            ,NF.ENT_CODIGO
                            ,NF.ENT_NOME
                            ,PAIS.PAIS_NOME
                            ,NF.PART_REPRESENTANTE_CODIGO
                            ,REP.PART_NOME
                            ,COND_PGTO.CPA_DESCRICAO

                    )DADOS_NOTA
                    LEFT JOIN DAV ON DAV.DAV_NUMERO = DADOS_NOTA.DAV_NUMERO
                    LEFT JOIN DAV_ITENS ON DAV_ITENS.DAV_NUMERO = DAV.DAV_NUMERO
                                        AND DAV_ITENS.DAVI_QTDE_ENTREGA = 0


                /*******************ROMANEIO*****************************************************************************/
                    UNION ALL
                    Select
                        'SISTEMA' 						 		    AS ORIGEM
                        ,'VENDA' 						 		    AS TIPO
                        ,DAV.DAV_TIPO 					  	        AS TIPO_RECEITA
                        ,'DECORBRAS' 			   				    AS EMPRESA
                        ,DAV.DAV_NUMERO_AUXILIAR	   	   	   	    AS DOCUMENTO
                        ,''	   		   				   			    AS SERIE_FAT
                        ,DAV.DAV_DATA_EMISSAO		   	 	   	    AS DATA_COMP
                        ,DAV.DAV_NUMERO_AUXILIAR				    AS NUM_PED
                        ,DAV.DAV_DATA_EMISSAO					    AS DATA_PED
                        ,DAV.ENT_CODIGO		   					    AS COD_CLIENTE
                        ,DAV_NOME_ADQUIRENTE	   	  			    AS CLIENTE
                        ,DAV_ENDERECO_CEP   				   	    AS CEP_IBGE
                        ,MUNICIPIO.MUN_NOME					   	    AS MUNICIPIO
                        ,MUNICIPIO.UF_CODIGO			            AS ESTADO
                        ,''							  			    AS PAIS
                        ,DAV.PART_REPRESENTANTE_CODIGO			    AS COD_VENDEDOR
                        ,REP.PART_NOME	                            AS VENDEDOR
                        ,CONDICOES_PAGAMENTO.CPA_DESCRICAO		    AS FORMA_DE_PAGTO
                        ,DAV_ITENS.PRD_CODIGO					    AS COD_PRODUTO
                        ,DAV_ITENS.DAVI_DESCRICAO  				    AS PRODUTO
                        ,DAV_ITENS.DAVI_UM						    AS UN_MEDIDA
                        ,DAV_ITENS.DAVI_QTDE  			  		    AS QUANTIDADE
                        ,0								    	    AS NI_VL_UNITARIO
                        ,DAVI_VL_UNIT
                        ,0								 		    AS FAT_BRUTO
                        ,DAV_ITENS.DAVI_VL_TOTAL		  	        AS FAT_BRUTO_2
                        ,DAV_ITENS.DAVI_VL_DESCONTO		 	   	    AS DESCONTO
                        ,DAV_ITENS.DAVI_VL_ACRESCIMO			    AS ACRESCIMO
                        ,DAV_ITENS.DAVI_VL_TOTAL                    AS FAT_LIQUIDO
                        ,0				                            AS IPI
                        ,DAV_ITENS.DAVI_COFINS_VALOR			    AS COFINS
                        ,DAV_ITENS.DAVI_ICMS_VALOR          	    AS ICMS
                        ,0									 	    AS CSLL
                        ,0										    AS IRPJ
                        ,0										    AS ISS
                        ,DAV_ITENS.DAVI_COMISSAO_PERC			    AS PERC_COMISSAO
                        ,DAV_ITENS.DAVI_VALOR_COMISSAO              AS COMISSAO
                        ,DAV_ITENS.DAVI_FRETE_VALOR_TOTAL		    AS FRETE
                        ,DAV.DAV_VALOR_FRETE                        AS FRETE_DAV

                    FROM DAV
                    LEFT JOIN DAV_ITENS		ON DAV_ITENS.DAV_NUMERO = DAV.DAV_NUMERO
                    LEFT JOIN MUNICIPIO		ON MUNICIPIO.MUN_CODIGO = DAV.MUN_CODIGO
                    LEFT JOIN PARTICIPANTE REP	ON REP.PART_REPRESENTANTE_CODIGO = DAV.PART_REPRESENTANTE_CODIGO
                    LEFT JOIN CONDICOES_PAGAMENTO ON CONDICOES_PAGAMENTO.CPA_CODIGO = DAV.CON_TIPO_PAGAMENTO

                    WHERE 1=1
                    AND DSP_CODIGO = 1
                    $data_dav
                    $docdav
                    $rep_cod1
            )vendas
            where 1 = 1
            $cliente
            $vendedor

            GROUP BY
                ORIGEM
                , TIPO
                , TIPO_RECEITA
                , EMPRESA
                , DOCUMENTO
                , SERIE_FAT
                , DATA_COMP
                , NUM_PED
                , DATA_PED
                , COD_CLIENTE
                , CLIENTE
                , CEP_IBGE
                , MUNICIPIO
                , ESTADO
                , PAIS
                , COD_VENDEDOR
                , VENDEDOR
                , FORMA_DE_PAGTO
                , FRETE_DAV
        ";
        // dd($sql);
        $vendas = DB::connection(env('APP_NAME'))->select($sql);
        return $vendas;
    }



}
