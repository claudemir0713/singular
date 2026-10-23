<?php
namespace App\Helpers;

use App\Models\FIN_CONTAS;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Pagar {
    public static function pagar($dataI,$dataF,$forencedor,$tipo) {
        if($dataI){
            $dataI = " AND CON_DT_VENCIMENTO >= '$dataI'";
        }else{
            $dataI ='';
        };
        if($dataF){
            $dataF = " AND CON_DT_VENCIMENTO <= '$dataF'";
        }else{
            $dataF ='';
        }
        if($forencedor){
            $forencedor = " AND .PART_NOME like '%$forencedor%'";
        }else{
            $forencedor ='';
        }
        if($tipo){
            if($tipo=='PD'){
                $tipo = " AND RIGHT(TRIM(CON_NUMERO),2) IN ('PD','CH')";
            }elseif($tipo!='PD'){
                $tipo = " AND RIGHT(TRIM(CON_NUMERO),2) NOT IN ('PD','CH')";
            }
        }else{
            $tipo = '';
        }
        $sql="
            SELECT
                CON_NUMERO
                ,  CON_SEQUENCIA
                ,  CON_DT_INCLUSAO
                ,  CON_DT_VENCIMENTO
                ,  CON_VALOR_CORRIGIDO as CON_VALOR_ORIGINAL
                ,  CON_VALOR_JUROS
                ,  CON_VALOR_MULTA
                ,  CON_VALOR_DESCONTO
                ,  PART_NOME
                ,  CON_TIPO_DESC                          AS TIPO_PGTO

            FROM  FIN_CONTAS
            LEFT JOIN  PARTICIPANTE  ON  PARTICIPANTE.PART_CODIGO  =  FIN_CONTAS.PART_CODIGO
            LEFT JOIN Fin_contas_tipos AS TIPO_PGTO ON TIPO_PGTO.CON_TIPO_PAGAMENTO = FIN_CONTAS.CON_TIPO_PAGAMENTO

            WHERE CON_TIPO  = 1
            AND  CON_PREVISAO  = 0
            AND  CON_SITUACAO  IN (0, 1)
            $dataI
            $dataF
            $forencedor
            $tipo
        ";
        // dd($sql);
        $pagar = db::connection(env('APP_NAME'))->select($sql);
        return $pagar;

    }


}
