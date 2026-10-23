<?php
namespace App\Helpers;

use App\Models\COMPRAS;
use App\Models\EMPRESA;
use App\Models\NF_VENDA_CABECALHO;
use App\Models\PRODUTOS;
use App\Models\singular_estoque_bloco_k;
use App\Models\SPED_ESTOQUE_TEMP;
use Illuminate\Support\Facades\DB;


class Sped {
    public static function Registro_0000($dataI,$dataF,$cod_ver,$cod_fin) {

        $EMP = EMPRESA::first(['EMPRESA_NOME','EMPRESA_CNPJ','EMPRESA_UF','EMPRESA_IE','MUN_CODIGO','EMPRESA_INSCRICAO_MUNICIPAL','PR_SPED_PERFIL','PR_TIPO_ATIVIDADE']);

        $REG        = '0000';
        $COD_VER    = trim($cod_ver);
        $COD_FIN    = trim($cod_fin);
        $DT_INI     = date('dmY',strtotime($dataI));
        $DT_FIN     = date('dmY',strtotime($dataF));
        $NOME       = trim($EMP->EMPRESA_NOME);
        $CNPJ       = trim($EMP->EMPRESA_CNPJ);
        $CNPJ       = preg_replace('/[^0-9]/', '', $CNPJ);
        $CPF        = '';
        $UF         = trim($EMP->EMPRESA_UF);
        $IE         = trim($EMP->EMPRESA_IE);
        $IE         = preg_replace('/[^0-9]/', '', $IE);
        $COD_MUN    = trim($EMP->MUN_CODIGO);
        $IM         = trim($EMP->EMPRESA_INSCRICAO_MUNICIPAL);
        $SUFRAMA    = '';
        $IND_PERFIL = trim($EMP->PR_SPED_PERFIL);
        $IND_ATIV   = trim($EMP->PR_TIPO_ATIVIDADE);

        $qtd = 1;
        $txt = '|'.$REG.'|'.$COD_VER.'|'.$COD_FIN.'|'.$DT_INI.'|'.$DT_FIN.'|'.$NOME.'|'.$CNPJ.'|'.$CPF.'|'.$UF.'|'.$IE.'|'.$COD_MUN.'|'.$IM.'|'.$SUFRAMA.'|'.$IND_PERFIL.'|'.$IND_ATIV.'|'."\r\n";
        $return = [
            'txt'           => $txt
            ,'qtd'          => $qtd
        ];
        return $return;
    }

    public static function Registro_0001() {
        $REG        = '0001';
        $IND_MOV    = 0;
        $txt = '|'.$REG.'|'.$IND_MOV.'|'."\r\n";
        $qtd = 1;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_0002() {
        $REG        = '0002';
        $CLAS_ESTAB_IND    = '00';
        $txt = '|'.$REG.'|'.$CLAS_ESTAB_IND.'|'."\r\n";
        $qtd = 1;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_0005() {
        $EMP = EMPRESA::first(['EMPRESA_NOME_FANTASIA','EMPRESA_CEP','EMPRESA_ENDERECO','EMPRESA_END_NUMERO','EMPRESA_COMPLEMENTO','EMPRESA_BAIRRO','EMPRESA_TELEFONE','EMPRESA_EMAIL']);
        $REG        = '0005';
        $FANTASIA   = $EMP->EMPRESA_NOME_FANTASIA;
        $CEP        = $EMP->EMPRESA_CEP;
        $END        = $EMP->EMPRESA_ENDERECO;
        $NUM        = $EMP->EMPRESA_END_NUMERO;
        $COMPL      = $EMP->EMPRESA_COMPLEMENTO;
        $BAIRRO     = $EMP->EMPRESA_BAIRRO;
        $FONE       = $EMP->CONTABILIDADE_FONE;
        $FAX        = $EMP->CONTABILIDADE_FONE;
        $EMAIL      = $EMP->EMPRESA_EMAIL;

        $txt = '|'.$REG.'|'.$FANTASIA.'|'.$CEP.'|'.$END.'|'.$NUM.'|'.$COMPL.'|'.$BAIRRO.'|'.$FONE.'|'.$FAX.'|'.$EMAIL.'|'."\r\n";
        $qtd = 1;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_0100() {
        $EMP = EMPRESA::first(['CONTABILISTA_NOME','CONTABILISTA_CPF','CONTABILISTA_CRC','CONTABILIDADE_CNPJ','CONTABILIDADE_CEP','CONTABILIDADE_ENDERECO','CONTABILIDADE_NUMERO','CONTABILIDADE_COMPLEMENTO','CONTABILIDADE_BAIRRO','CONTABILIDADE_FONE','CONTABILIDADE_EMAIL','CONTABILIDADE_MUN_CODIGO']);
        $REG       = '0100';
        $NOME      = $EMP->CONTABILISTA_NOME;
        $CPF       = $EMP->CONTABILISTA_CPF;
        $CPF       = preg_replace('/[^0-9]/', '', $CPF);
        $CRC       = preg_replace('/[^0-9]/', '',$EMP->CONTABILISTA_CRC);
        $CNPJ      = $EMP->CONTABILIDADE_CNPJ;
        $CNPJ      = preg_replace('/[^0-9]/', '', $CNPJ);
        $CEP       = $EMP->CONTABILIDADE_CEP;
        $CEP       = preg_replace('/[^0-9]/', '', $CEP);
        $END       = $EMP->CONTABILIDADE_ENDERECO;
        $NUM       = $EMP->CONTABILIDADE_NUMERO;
        $COMPL     = $EMP->CONTABILIDADE_COMPLEMENTO;
        $BAIRRO    = $EMP->CONTABILIDADE_BAIRRO;
        $FONE      = $EMP->CONTABILIDADE_FONE;
        $FONE      = preg_replace('/[^0-9]/', '', $FONE);
        $FAX       = '';
        $EMAIL     = $EMP->CONTABILIDADE_EMAIL;
        $COD_MUN   = $EMP->CONTABILIDADE_MUN_CODIGO;

        $txt = '|'.$REG.'|'.$NOME.'|'.$CPF.'|'.$CRC.'|'.$CNPJ.'|'.$CEP.'|'.$END.'|'.$NUM.'|'.$COMPL.'|'.$BAIRRO.'|'.$FONE.'|'.$FAX.'|'.$EMAIL.'|'.$COD_MUN.'|'."\r\n";
        $qtd = 1;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }
    public static function nfSaida($dataI,$dataF){
        $filtrosV = [];
        $filtrosV[]=['NF_SITUACAO','1'];
        $filtrosV[]=['NF_DT_EMISSAO','>=',$dataI];
        $filtrosV[]=['NF_DT_EMISSAO','<=',$dataF];

        $NF_SAIDA = NF_VENDA_CABECALHO::where($filtrosV)
                                        ->whereIn('CFOP_GRUPOS.CFGR_CODIGO',[1])
                                        ->leftJoin('PARTICIPANTE','PARTICIPANTE.PART_CLIENTE_CODIGO','NF_VENDA_CABECALHO.ENT_CODIGO')
                                        ->leftJoin('NF_VENDA_ITENS','NF_VENDA_ITENS.NF_NUMERO','NF_VENDA_CABECALHO.NF_NUMERO')
                                        ->leftJoin('UNIDADES_MEDIDA','UNIDADES_MEDIDA.UM_CODIGO','NF_VENDA_ITENS.NI_UNIMED')
                                        ->leftJoin('PRODUTOS','PRODUTOS.PRD_CODIGO','NF_VENDA_ITENS.PRD_CODIGO')
                                        ->leftJoin('CFOP','CFOP.CFOP_CODIGO','NF_VENDA_ITENS.CFOP_CODIGO')
                                        ->leftJoin('CFOP_GRUPOS','CFOP_GRUPOS.CFGR_CODIGO','CFOP.CFGR_CODIGO')
                                        ->get([
                                            'NF_VENDA_CABECALHO.ENT_CODIGO'
                                            ,'PARTICIPANTE.PART_NOME'
                                            ,'PART_TIPO_PESSOA'
                                            ,'PARTICIPANTE.PAIS_CODIGO'
                                            ,'PARTICIPANTE.PART_CNPJ_CPF'
                                            ,'PARTICIPANTE.PART_IE_RG'
                                            ,'PARTICIPANTE.MUN_CODIGO'
                                            ,'PARTICIPANTE.PART_SUFRAMA'
                                            ,'PARTICIPANTE.PART_ENDERECO_LOGRADOURO'
                                            ,'PARTICIPANTE.PART_ENDERECO_NUMERO'
                                            ,'PARTICIPANTE.PART_ENDERECO_COMPLEMENTO'
                                            ,'PARTICIPANTE.PART_ENDERECO_BAIRRO'
                                            ,'UNIDADES_MEDIDA.UM_CODIGO'
                                            ,'UNIDADES_MEDIDA.UM_DESCRICAO'
                                            ,'PRODUTOS.PRD_CODIGO AS COD_ITEM'
                                            ,'PRODUTOS.PRD_DESCRICAO AS DESCR_ITEM'
                                            ,DB::raw("'' AS COD_BARRA")
                                            ,DB::raw("'' AS COD_ANT_ITEM")
                                            ,'PRODUTOS.UM_CODIGO AS UNID_INV'
                                            ,'PRODUTOS.PRD_TIPO_PRODUTO AS TIPO_ITEM'
                                            ,'PRODUTOS.NCM_CODIGO AS COD_NCM'
                                            ,'PRODUTOS.PRD_EX_TIPI AS EX_IPI'
                                            ,DB::raw("'' AS COD_GEN")
                                            ,DB::raw("'' AS COD_LST")
                                            ,'PRODUTOS.PRD_ICMS_ALIQUOTA AS ALIQ_ICMS'
                                            ,'PRODUTOS.PRD_CST_ICMS AS CEST'
                                            ,'PRODUTOS.PRD_UM_CODIGO_COMPRA AS UNID_CONV'
                                            ,'PRODUTOS.PRD_UM_FATOR_CONVERSAO AS FAT_CONV'
                                        ]);
        return $NF_SAIDA;
    }

    public static function nfEntrada($dataI,$dataF){
        $filtrosC = [];
        $filtrosC[] = ['CMP_SITUACAO',1];
        $filtrosC[]=['CMP_DT_INCLUSAO','>=',$dataI];
        $filtrosC[]=['CMP_DT_INCLUSAO','<=',$dataF];

        $NF_COMPRA = COMPRAS::where($filtrosC)
                            ->whereNull('CMP_DT_CANCELAMENTO')
                            ->whereIn('CFOP_GRUPOS.CFGR_CODIGO',[51])
                            ->leftJoin('PARTICIPANTE','PARTICIPANTE.PART_FORNECEDOR_CODIGO','COMPRAS.ENT_CODIGO')
                            ->leftJoin('COMPRAS_ITENS','COMPRAS_ITENS.CMP_NUMERO','COMPRAS.CMP_NUMERO')
                            ->leftJoin('UNIDADES_MEDIDA','UNIDADES_MEDIDA.UM_CODIGO','COMPRAS_ITENS.CMI_UM_COMPRA')
                            ->leftJoin('PRODUTOS','PRODUTOS.PRD_CODIGO','COMPRAS_ITENS.PRD_CODIGO')
                            ->leftJoin('CFOP','CFOP.CFOP_CODIGO','COMPRAS_ITENS.CFOP_CODIGO')
                            ->leftJoin('CFOP_GRUPOS','CFOP_GRUPOS.CFGR_CODIGO','CFOP.CFGR_CODIGO')

                            ->get([
                                'COMPRAS.ENT_CODIGO'
                                ,'PARTICIPANTE.PART_NOME'
                                ,'PART_TIPO_PESSOA'
                                ,'PARTICIPANTE.PAIS_CODIGO'
                                ,'PARTICIPANTE.PART_CNPJ_CPF'
                                ,'PARTICIPANTE.PART_IE_RG'
                                ,'PARTICIPANTE.MUN_CODIGO'
                                ,'PARTICIPANTE.PART_SUFRAMA'
                                ,'PARTICIPANTE.PART_ENDERECO_LOGRADOURO'
                                ,'PARTICIPANTE.PART_ENDERECO_NUMERO'
                                ,'PARTICIPANTE.PART_ENDERECO_COMPLEMENTO'
                                ,'PARTICIPANTE.PART_ENDERECO_BAIRRO'
                                ,'UNIDADES_MEDIDA.UM_CODIGO'
                                ,'UNIDADES_MEDIDA.UM_DESCRICAO'
                                ,'PRODUTOS.PRD_CODIGO AS COD_ITEM'
                                ,'PRODUTOS.PRD_DESCRICAO AS DESCR_ITEM'
                                ,DB::raw("'' AS COD_BARRA")
                                ,DB::raw("'' AS COD_ANT_ITEM")
                                ,'PRODUTOS.UM_CODIGO AS UNID_INV'
                                ,'PRODUTOS.PRD_TIPO_PRODUTO AS TIPO_ITEM'
                                ,'PRODUTOS.NCM_CODIGO AS COD_NCM'
                                ,'PRODUTOS.PRD_EX_TIPI AS EX_IPI'
                                ,DB::raw("'' AS COD_GEN")
                                ,DB::raw("'' AS COD_LST")
                                ,'PRODUTOS.PRD_ICMS_ALIQUOTA AS ALIQ_ICMS'
                                ,'PRODUTOS.PRD_CST_ICMS AS CEST'
                                ,'PRODUTOS.PRD_UM_CODIGO_COMPRA AS UNID_CONV'
                                ,'PRODUTOS.PRD_UM_FATOR_CONVERSAO AS FAT_CONV'

                            ]);
        return $NF_COMPRA;
    }

    public static function estoqueK($dataI,$dataF){
        // $EST = singular_estoque_bloco_k::where('qtd','>=',0)->where('data','>=',$dataI)->where('data','<=',$dataF)->get();
        $EST = SPED_ESTOQUE_TEMP::where('SET_PRD_ESTOQUE','>=',0)->where('SET_PERIODO','>=',$dataI)->where('SET_PERIODO','<=',$dataF)->get();
        // dd($EST);
        return $EST;
    }
    public static function estoque($dataI,$dataF){
        // $EST = singular_estoque_bloco_k::leftJoin('produto_mysql','produto_mysql.prd_codigo','singular_estoque_bloco_k.prd_codigo')
        //         ->where('data','>=',$dataI)
        //         ->where('data','<=',$dataF)
        //         ->where('qtd','>=',0)
        //         ->get([
        //             'produto_mysql.prd_codigo'
        //             ,'produto_mysql.prd_descricao'
        //             ,db::raw("'' as cod_barra")
        //             ,db::raw("'' as cod_ant_item")
        //             ,'um_codigo'
        //             ,'prd_tipo_produto'
        //             ,'ncm_codigo'
        //             ,db::raw("'' as ex_ipi")
        //             ,db::raw("'' as cod_gen")
        //             ,db::raw("'' as cod_lst")
        //             ,db::raw("'' as aliq_icms")
        //             ,db::raw("'' as cest")
        //         ]);

        $EST = SPED_ESTOQUE_TEMP::leftJoin('PRODUTOS','PRODUTOS.PRD_CODIGO','SPED_ESTOQUE_TEMP.PRD_CODIGO')
                ->where('SET_PERIODO','>=',$dataI)
                ->where('SET_PERIODO','<=',$dataF)
                ->where('SET_PRD_ESTOQUE','>=',0)
                ->get([
                    db::raw("SPED_ESTOQUE_TEMP.PRD_CODIGO as prd_codigo")
                    ,db::raw("SPED_ESTOQUE_TEMP.PRD_DESCRICAO as prd_descricao")
                    ,db::raw("'' as cod_barra")
                    ,db::raw("'' as cod_ant_item")
                    ,db::raw("PRODUTOS.UM_CODIGO as um_codigo")
                    ,db::raw("PRODUTOS.PRD_TIPO_PRODUTO as prd_tipo_produto")
                    ,db::raw("PRODUTOS.NCM_CODIGO as ncm_codigo")
                    ,db::raw("'' as ex_ipi")
                    ,db::raw("'' as cod_gen")
                    ,db::raw("'' as cod_lst")
                    ,db::raw("'' as aliq_icms")
                    ,db::raw("'' as cest")
                ]);


        return $EST;
    }

    public static function Registro_0150($dataI,$dataF) {
        $Arr_Registro_0150=[];
        /*****************************notas de saida**************************************/
            // $Registro_0150='';
            // $REG_150 = Sped::nfSaida($dataI,$dataF);
            // foreach($REG_150 AS $ITEM){
            //     $REG        ='0150';
            //     $COD_PART   = $ITEM->ENT_CODIGO;
            //     $NOME       = $ITEM->PART_NOME;
            //     $COD_PAIS   = $ITEM->PAIS_CODIGO;
            //     if($ITEM->PART_TIPO_PESSOA=='J'){
            //         $CNPJ       = $ITEM->PART_CNPJ_CPF;
            //         $CNPJ       = preg_replace('/[^0-9]/', '', $CNPJ);
            //         $CPF        = '';
            //     }else{
            //         $CPF       = $ITEM->PART_CNPJ_CPF;
            //         $CPF       = preg_replace('/[^0-9]/', '', $CPF);
            //         $CNPJ      = '';
            //     };
            //     $IE         = $ITEM->PART_IE_RG;
            //     $IE         = preg_replace('/[^0-9]/', '', $IE);
            //     $COD_MUN    = $ITEM->MUN_CODIGO;
            //     $SUFRAMA    = $ITEM->PART_SUFRAMA;
            //     $END        = $ITEM->PART_ENDERECO_LOGRADOURO;
            //     $NUM        = $ITEM->PART_ENDERECO_NUMERO;
            //     $COMPL      = $ITEM->PART_ENDERECO_COMPLEMENTO;
            //     $BAIRRO     = $ITEM->PART_ENDERECO_BAIRRO;
            //     $Arr_Registro_0150[]= '|'.$REG.'|'.$COD_PART.'|'.$NOME.'|'.$COD_PAIS.'|'.$CNPJ.'|'.$CPF.'|'.$IE.'|'.$COD_MUN.'|'.$SUFRAMA.'|'.$END.'|'.$NUM.'|'.$COMPL.'|'.$BAIRRO.'|';
            // }

        /*****************************notas de entrada**************************************/
            // $REG_150 = Sped::nfEntrada($dataI,$dataF);
            // foreach($REG_150 AS $ITEM){
            //     $REG        ='0150';
            //     $COD_PART   = $ITEM->ENT_CODIGO;
            //     $NOME       = $ITEM->PART_NOME;
            //     $COD_PAIS   = $ITEM->PAIS_CODIGO;
            //     if($ITEM->PART_TIPO_PESSOA=='J'){
            //         $CNPJ       = $ITEM->PART_CNPJ_CPF;
            //         $CNPJ       = preg_replace('/[^0-9]/', '', $CNPJ);
            //         $CPF        = '';
            //     }else{
            //         $CPF       = $ITEM->PART_CNPJ_CPF;
            //         $CPF       = preg_replace('/[^0-9]/', '', $CPF);
            //         $CNPJ      = '';
            //     };
            //     $IE         = $ITEM->PART_IE_RG;
            //     $IE         = preg_replace('/[^0-9]/', '', $IE);
            //     $COD_MUN    = $ITEM->MUN_CODIGO;
            //     $SUFRAMA    = $ITEM->PART_SUFRAMA;
            //     $END        = $ITEM->PART_ENDERECO_LOGRADOURO;
            //     $NUM        = $ITEM->PART_ENDERECO_NUMERO;
            //     $COMPL      = $ITEM->PART_ENDERECO_COMPLEMENTO;
            //     $BAIRRO     = $ITEM->PART_ENDERECO_BAIRRO;
            //     $Arr_Registro_0150[]= '|'.$REG.'|'.$COD_PART.'|'.$NOME.'|'.$COD_PAIS.'|'.$CNPJ.'|'.$CPF.'|'.$IE.'|'.$COD_MUN.'|'.$SUFRAMA.'|'.$END.'|'.$NUM.'|'.$COMPL.'|'.$BAIRRO.'|';
            // }
        /*************************************************************************************/
        $txt ='';
        $qtd = 0;
        // foreach( array_unique($Arr_Registro_0150) as $item){
        //     $txt .=$item."\r\n";
        //     $qtd ++;
        // }
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_0190($dataI,$dataF) {
        $Arr_Registro_0190=[];
        /*****************************notas de saida**************************************/
            // $Registro_0190='';
            // $REG_150 = Sped::nfSaida($dataI,$dataF);
            // foreach($REG_150 AS $ITEM){
            //     $REG                ='0190';
            //     $UNID               = $ITEM->UM_CODIGO;
            //     $DESCR              = $ITEM->UM_DESCRICAO;
            //     if($ITEM->UM_CODIGO){
            //         $Arr_Registro_0190[]= '|'.$REG.'|'.$UNID.'|'.$DESCR.'|';
            //     }
            // }
        /*****************************notas de entrada**************************************/
            // $Registro_0190='';
            // $REG_150 = Sped::nfEntrada($dataI,$dataF);
            // foreach($REG_150 AS $ITEM){
            //     $REG                ='0190';
            //     $UNID               = $ITEM->UM_CODIGO;
            //     $DESCR              = $ITEM->UM_DESCRICAO;
            //     if($ITEM->UM_CODIGO){
            //         $Arr_Registro_0190[]= '|'.$REG.'|'.$UNID.'|'.$DESCR.'|';
            //     }
            // }

        /*****************************blocoK**************************************/
        $Registro_0190='';
        $REG_150 = Sped::estoque($dataI,$dataF);
        foreach($REG_150 AS $ITEM){
            $REG                ='0190';
            $UNID               = $ITEM->um_codigo;
            $DESCR              = $ITEM->um_codigo;
            if($ITEM->um_codigo){
                $Arr_Registro_0190[]= '|'.$REG.'|'.$UNID.'|'.$DESCR.'|';
            }
        }
        /*************************************************************************************/
            $txt ='';
            $qtd = 0;
            foreach( array_unique($Arr_Registro_0190) as $item){
                $txt .=$item."\r\n";
                $qtd ++;
            }
            $return = [
                'txt'   => $txt
                ,'qtd'  => $qtd
            ];
            return $return;
    }
    public static function Registro_0200($dataI,$dataF) {
        $Arr_Registro_0200=[];
        /*****************************notas de saida**************************************/
            // $REG_0200 = Sped::nfSaida($dataI,$dataF);
            // foreach($REG_0200 AS $ITEM){
            //     $REG                ='0200';
            //     $COD_ITEM           = $ITEM->COD_ITEM;
            //     $DESCR_ITEM         = $ITEM->DESCR_ITEM;
            //     $COD_BARRA          = '';
            //     $COD_ANT_ITEM       = '';
            //     $UNID_INV           = $ITEM->UNID_INV;
            //     $TIPO_ITEM          = $ITEM->TIPO_ITEM;
            //     $COD_NCM            = $ITEM->COD_NCM;
            //     $COD_NCM            = preg_replace('/[^0-9]/', '', $COD_NCM);
            //     $EX_IPI             = '';
            //     $COD_GEN            = '';
            //     $COD_LST            = '';
            //     $ALIQ_ICMS          = '';
            //     $CEST               = '';
            //     if($ITEM->COD_ITEM){
            //         // $Arr_Registro_0200[]= '|'.$REG.'|'.$COD_ITEM.'|'.$DESCR_ITEM.'|'.$COD_BARRA.'|'.$COD_ANT_ITEM.'|'.$UNID_INV.'|'.$TIPO_ITEM.'|'.$COD_NCM.'|'.$EX_IPI.'|'.$COD_GEN.'|'.$COD_LST.'|'.$ALIQ_ICMS.'|'.$CEST.'|';
            //     }
            // }
        /*****************************notas de entrada**************************************/
            // $REG_0200 = Sped::nfEntrada($dataI,$dataF);
            // foreach($REG_0200 AS $ITEM){
            //     $REG                ='0200';
            //     $COD_ITEM           = $ITEM->COD_ITEM;
            //     $DESCR_ITEM         = $ITEM->DESCR_ITEM;
            //     $COD_BARRA          = '';
            //     $COD_ANT_ITEM       = '';
            //     $UNID_INV           = $ITEM->UNID_INV;
            //     $TIPO_ITEM          = $ITEM->TIPO_ITEM;
            //     $COD_NCM            = $ITEM->COD_NCM;
            //     $COD_NCM            = preg_replace('/[^0-9]/', '', $COD_NCM);
            //     $EX_IPI             = '';
            //     $COD_GEN            = '';
            //     $COD_LST            = '';
            //     $ALIQ_ICMS          = '';
            //     $CEST               = '';
            //     if($ITEM->COD_ITEM){
            //         $Arr_Registro_0200[]= '|'.$REG.'|'.$COD_ITEM.'|'.$DESCR_ITEM.'|'.$COD_BARRA.'|'.$COD_ANT_ITEM.'|'.$UNID_INV.'|'.$TIPO_ITEM.'|'.$COD_NCM.'|'.$EX_IPI.'|'.$COD_GEN.'|'.$COD_LST.'|'.$ALIQ_ICMS.'|'.$CEST.'|';
            //     }
            // }
        /*****************************blocoK**************************************/
            $REG_0200 = Sped::estoque($dataI,$dataF);
            foreach($REG_0200 AS $ITEM){
                $REG                ='0200';
                $COD_ITEM           = $ITEM->PRD_CODIGO ;
                $DESCR_ITEM         = $ITEM->PRD_DESCRICAO;
                $COD_BARRA          = '';
                $COD_ANT_ITEM       = '';
                $UNID_INV           = $ITEM->UM_CODIGO;
                $TIPO_ITEM          = $ITEM->PRD_TIPO_PRODUTO;
                $COD_NCM            = $ITEM->NCM_CODIGO;
                $COD_NCM            = preg_replace('/[^0-9]/', '', $COD_NCM);
                $EX_IPI             = '';
                $COD_GEN            = '';
                $COD_LST            = '';
                $ALIQ_ICMS          = '';
                $CEST               = '';
                $PRO_TOTVS          = null;

                if($ITEM->PRD_CODIGO){
                    $Arr_Registro_0200[]= '|'.$REG.'|'.$COD_ITEM.'|'.$DESCR_ITEM.'|'.$COD_BARRA.'|'.$COD_ANT_ITEM.'|'.$UNID_INV.'|'.$TIPO_ITEM.'|'.$COD_NCM.'|'.$EX_IPI.'|'.$COD_GEN.'|'.$COD_LST.'|'.$ALIQ_ICMS.'|'.$CEST.'|';
                }
                // print_r($COD_ITEM.'<br>');
            }
        /*************************************************************************************/
            $txt = '';
            $qtd = 0;
            foreach( array_unique($Arr_Registro_0200) as $item){
                $txt .=$item."\r\n";
                $qtd ++;
            }
            $return = [
                'txt'   => $txt
                ,'qtd'  => $qtd
            ];
            return $return;
    }

    public static function Registro_0220($dataI,$dataF) {
        $Arr_Registro_0220=[];
        /*****************************notas de saida**************************************/
            // $REG_0220 = Sped::nfSaida($dataI,$dataF);
            // foreach($REG_0220 AS $ITEM){
            //     $REG               ='0220';
            //     $UNID_CONV         = $ITEM->UNID_CONV;
            //     $FAT_CONV          = number_format($ITEM->FAT_CONV,2,',','');
            //     $COD_BARRA         = $ITEM->COD_BARRA;
            //     if($ITEM->COD_ITEM){
            //         $Arr_Registro_0220[]= '|'.$REG.'|'.$UNID_CONV.'|'.$FAT_CONV.'|'.$COD_BARRA.'|';
            //     }
            // }
        /*****************************notas de entrada**************************************/
            // $REG_0220 = Sped::nfEntrada($dataI,$dataF);
            // foreach($REG_0220 AS $ITEM){
            //     $REG               ='0220';
            //     $UNID_CONV         = $ITEM->UNID_CONV;
            //     $FAT_CONV          = number_format($ITEM->FAT_CONV,2,',','');
            //     $COD_BARRA         = $ITEM->COD_BARRA;
            //     if($ITEM->COD_ITEM){
            //         $Arr_Registro_0220[]= '|'.$REG.'|'.$UNID_CONV.'|'.$FAT_CONV.'|'.$COD_BARRA.'|';
            //     }
            // }

        /*************************************************************************************/
            $txt = '';
            $qtd = 0;
            foreach( array_unique($Arr_Registro_0220) as $item){
                $txt .=$item."\r\n";
                $qtd ++;
            }
            $return = [
                'txt'   => $txt
                ,'qtd'  => $qtd
            ];
            return $return;
    }

    public static function Registro_0990($QTD_LIN_0990){
        $REG        = '0990';
        $QTD_LIN_0  = $QTD_LIN_0990;
        $txt = '|'.$REG.'|'.$QTD_LIN_0.'|'."\r\n";
        $qtd = 1;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_B001(){
        $REG        = 'B001';
        $IND_DAD    = '1';
        $txt = '|'.$REG.'|'.$IND_DAD.'|'."\r\n";
        $qtd = 1;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }
    public static function Registro_B990($QTD_LIN_B990){
        $REG        = 'B990';
        $IND_DAD    = $QTD_LIN_B990;
        $txt = '|'.$REG.'|'.$IND_DAD.'|'."\r\n";
        $qtd = 1;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_C001(){
        $REG        = 'C001';
        $IND_MOV    = 1; /*verificar se tem registro 0 se não 1*/
        $txt = '|'.$REG.'|'.$IND_MOV.'|'."\r\n";
        $qtd = 1;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_C990($QTD_LIN_C990){
        $REG        = 'C990';
        $QTD_LIN_C  = $QTD_LIN_C990;
        $txt = '|'.$REG.'|'.$QTD_LIN_C.'|'."\r\n";
        $qtd = $QTD_LIN_C990;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_D001(){
        $REG        = 'D001';
        $IND_MOV    = 1; /*verificar se tem registro 0 se não 1*/
        $txt = '|'.$REG.'|'.$IND_MOV.'|'."\r\n";
        $qtd = 1;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_D990($QTD_LIN_D990){
        $REG        = 'D990';
        $QTD_LIN_C  = $QTD_LIN_D990;
        $txt = '|'.$REG.'|'.$QTD_LIN_C.'|'."\r\n";
        $qtd = $QTD_LIN_D990;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_E001(){
        $REG        = 'E001';
        $IND_MOV    = 1; /*verificar se tem registro 0 se não 1*/
        $txt = '|'.$REG.'|'.$IND_MOV.'|'."\r\n";
        $qtd = 1;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_E990($QTD_LIN_E990){
        $REG        = 'E990';
        $QTD_LIN_C  = $QTD_LIN_E990;
        $txt = '|'.$REG.'|'.$QTD_LIN_C.'|'."\r\n";
        $qtd = $QTD_LIN_E990;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_G001(){
        $REG        = 'G001';
        $IND_MOV    = 1; /*verificar se tem registro 0 se não 1*/
        $txt = '|'.$REG.'|'.$IND_MOV.'|'."\r\n";
        $qtd = 1;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_G990($QTD_LIN_G990){
        $REG        = 'G990';
        $QTD_LIN_C  = $QTD_LIN_G990;
        $txt = '|'.$REG.'|'.$QTD_LIN_C.'|'."\r\n";
        $qtd = $QTD_LIN_G990;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_H001(){
        $REG        = 'H001';
        $IND_MOV    = 1; /*verificar se tem registro 0 se não 1*/
        $txt = '|'.$REG.'|'.$IND_MOV.'|'."\r\n";
        $qtd = 1;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_H990($QTD_LIN_H990){
        $REG        = 'H990';
        $QTD_LIN_C  = $QTD_LIN_H990;
        $txt = '|'.$REG.'|'.$QTD_LIN_C.'|'."\r\n";
        $qtd = $QTD_LIN_H990;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_K001(){
        $REG        = 'K001';
        $IND_MOV    = 0; /*verificar se tem registro 0 se não 1*/
        $txt = '|'.$REG.'|'.$IND_MOV.'|'."\r\n";
        $qtd = 1;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_K100($dataI,$dataF){
        $REG        = 'K100';
        $DT_INI     = date('dmY',strtotime($dataI));
        $DT_FIN     = date('dmY',strtotime($dataF));
        $txt = '|'.$REG.'|'.$DT_INI.'|'.$DT_FIN.'|'."\r\n";
        $qtd = 1;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }
    public static function Registro_K200($dataI,$dataF){
        $Nqtd   = 0;
        $REG    = 'K200';
        $k200   = sped::estoqueK($dataI,$dataF);
        $DT_EST = date('dmY',strtotime($dataF));
        $txt    = '';
        foreach($k200 as $item){
            $COD_ITEM   = $item->PRD_CODIGO;
            $QTD        = number_format($item->SET_PRD_ESTOQUE,3,',','');
            $IND_EST    =  0;
            $COD_PART   = '';
            $txt .= '|'.$REG.'|'.$DT_EST.'|'.$COD_ITEM.'|'.$QTD.'|'.$IND_EST.'|'.$COD_PART.'|'."\r\n";
            $Nqtd ++ ;
        }
        $return = [
            'txt'   => $txt
            ,'qtd'  => $Nqtd
        ];
        return $return;
    }


    public static function Registro_K990($QTD_LIN_K990){
        $REG        = 'K990';
        $QTD_LIN_C  = $QTD_LIN_K990+2;
        $txt = '|'.$REG.'|'.$QTD_LIN_C.'|'."\r\n";
        $qtd = $QTD_LIN_K990;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_1001(){
        $REG        = '1001';
        $IND_MOV    = 1; /*verificar se tem registro 0 se não 1*/
        $txt = '|'.$REG.'|'.$IND_MOV.'|'."\r\n";
        $qtd = 1;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_1990($QTD_LIN_1990){
        $REG        = '1990';
        $QTD_LIN_C  = $QTD_LIN_1990;
        $txt = '|'.$REG.'|'.$QTD_LIN_C.'|'."\r\n";
        $qtd = $QTD_LIN_1990;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_9001(){
        $REG        = '9001';
        $IND_MOV    = 0; /*verificar se tem registro 0 se não 1*/
        $txt = '|'.$REG.'|'.$IND_MOV.'|'."\r\n";
        $qtd = 1;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_9900($QTD_LIN_0000,$QTD_LIN_0001,$QTD_LIN_0002,$QTD_LIN_0005,$QTD_LIN_0100,$QTD_LIN_0150,$QTD_LIN_0190,$QTD_LIN_0200,$QTD_LIN_0220,$QTD_LIN_0990,$QTD_LIN_B001,$QTD_LIN_C001,$QTD_LIN_D001,$QTD_LIN_E001,$QTD_LIN_G001,$QTD_LIN_H001,$QTD_LIN_K001,$QTD_LIN_K200,$QTD_LIN_1001,$QTD_LIN_1990){
        $txt = '';
        $qtd = 0;
        if($QTD_LIN_0000){
            $txt .= '|9900|0000|'.$QTD_LIN_0000.'|'."\r\n";
            $qtd = $qtd+1;
        }
        if($QTD_LIN_0001){
            $txt .= '|9900|0001|'.$QTD_LIN_0001.'|'."\r\n";
            $qtd = $qtd+1;
        }
        if($QTD_LIN_0002){
            $txt .= '|9900|0002|'.$QTD_LIN_0002.'|'."\r\n";
            $qtd = $qtd+1;
        }
        if($QTD_LIN_0005){
            $txt .= '|9900|0005|'.$QTD_LIN_0005.'|'."\r\n";
            $qtd = $qtd+1;
        }
        if($QTD_LIN_0000||$QTD_LIN_0001||$QTD_LIN_0002||$QTD_LIN_0005){
            $QTD_LIN_0990 = $QTD_LIN_0990;
            $txt .= '|9900|0990|'.($QTD_LIN_0990).'|'."\r\n";
            $qtd = $qtd+1;
        }
        if($QTD_LIN_0100){
            $txt .= '|9900|0100|'.$QTD_LIN_0100.'|'."\r\n";
            $qtd = $qtd+1;
        }
        if($QTD_LIN_0150){
            $txt .= '|9900|0150|'.$QTD_LIN_0150.'|'."\r\n";
            $qtd = $qtd+1;
        }
        if($QTD_LIN_0190){
            $txt .= '|9900|0190|'.$QTD_LIN_0190.'|'."\r\n";
            $qtd = $qtd+1;
        }
        if($QTD_LIN_0200){
            $txt .= '|9900|0200|'.$QTD_LIN_0200.'|'."\r\n";
            $qtd = $qtd+1;
        }
        if($QTD_LIN_0220){
            $txt .= '|9900|0220|'.$QTD_LIN_0220.'|'."\r\n";
            $qtd = $qtd+1;
        }
        if($QTD_LIN_B001){
            $txt .= '|9900|B001|1|'."\r\n";
            $txt .= '|9900|B990|'.$QTD_LIN_B001.'|'."\r\n";
            $qtd = $qtd+2;
        }
        if($QTD_LIN_C001){
            $txt .= '|9900|C001|1|'."\r\n";
            $txt .= '|9900|C990|'.$QTD_LIN_C001.'|'."\r\n";
            $qtd = $qtd+2;
        }
        if($QTD_LIN_D001){
            $txt .= '|9900|D001|1|'."\r\n";
            $txt .= '|9900|D990|'.$QTD_LIN_D001.'|'."\r\n";
            $qtd = $qtd+2;
        }
        if($QTD_LIN_E001){
            $txt .= '|9900|E001|1|'."\r\n";
            $txt .= '|9900|E990|'.$QTD_LIN_E001.'|'."\r\n";
            $qtd = $qtd+2;
        }
        if($QTD_LIN_G001){
            $txt .= '|9900|G001|1|'."\r\n";
            $txt .= '|9900|G990|'.$QTD_LIN_G001.'|'."\r\n";
            $qtd = $qtd+2;
        }
        if($QTD_LIN_H001){
            $txt .= '|9900|H001|1|'."\r\n";
            $txt .= '|9900|H990|'.$QTD_LIN_H001.'|'."\r\n";
            $qtd = $qtd+2;
        }
        if($QTD_LIN_K001){
            $txt .= '|9900|K001|1|'."\r\n";
            $txt .= '|9900|K100|1|'."\r\n";
            $txt .= '|9900|K200|'.$QTD_LIN_K200.'|'."\r\n";
            $QTD_LIN_K001 + 1;
            $txt .= '|9900|K990|'.$QTD_LIN_K001.'|'."\r\n";
            $qtd = $qtd+3;
        }

        if($QTD_LIN_1001){
            $txt .= '|9900|1001|'.$QTD_LIN_1001.'|'."\r\n";
            $qtd = $qtd+1;
        }
        if($QTD_LIN_1990){
            $txt .= '|9900|1990|'.$QTD_LIN_1990.'|'."\r\n";
            $qtd = $qtd+1;
        }

        $qtd = $qtd+5;
        $txt .= '|9900|9001|1|'."\r\n";
        $txt .= '|9900|9900|'.$qtd.'|'."\r\n";
        $txt .= '|9900|9990|1|'."\r\n";
        $txt .= '|9900|9999|1|'."\r\n";


        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_9990($QTD_LIN_9900){
        $REG            = '9990';
        $QTD_LIN_9900   =$QTD_LIN_9900;
        $txt = '|'.$REG.'|'.$QTD_LIN_9900.'|'."\r\n";
        $qtd = $QTD_LIN_9900;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

    public static function Registro_9999($QTD_LIN_9999){
        $REG        = '9999';
        $QTD_LIN    = $QTD_LIN_9999+1;
        $txt = '|'.$REG.'|'.$QTD_LIN.'|'."\r\n";
        $qtd = 1;
        $return = [
            'txt'   => $txt
            ,'qtd'  => $qtd
        ];
        return $return;
    }

}
