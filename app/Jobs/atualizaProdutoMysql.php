<?php

namespace App\Jobs;

use App\Models\produto_mysql;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class atualizaProdutoMysql implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sql = "
            SELECT
                PRD_CODIGO
                ,PRD_DESCRICAO
                ,UM_CODIGO
                ,PRD_TIPO_PRODUTO
                ,UM_CODIGO
                ,PRD_TIPO_PRODUTO
                ,NCM_CODIGO
            FROM PRODUTOS
        ";
        $Produto_singular = DB::connection('Decorbras')->select($sql);
        // $sql="
        //     SELECT
        //         CAST(LTRIM(RTRIM(B1_CODPROD)) AS INTEGER)				AS PRD_CODIGO
        //         ,LTRIM(RTRIM(B1_DESCRI)) 				AS PRD_DESCRICAO
        //         ,LTRIM(RTRIM(B1_CODGRP)) 				AS UM_CODIGO
        //         ,LTRIM(RTRIM(B1_TIPO)) 					AS PRD_TIPO_PRODUTO
        //         ,LTRIM(RTRIM(B1_CODUM)) 				AS UM_CODIGO
        //         ,LTRIM(RTRIM(B1_NCM)) 					AS NCM_CODIGO
        //     FROM SB1
        //     WHERE B1_FILIAL = '01'
        //     AND D_E_L_E_T_ <> '*'
        //     AND LTRIM(RTRIM(B1_TIPO)) NOT IN ('6')
        // ";
        // $Produto_singular = DB::connection('totvs')->select($sql);

        $x = 0;
        // produto_mysql::truncate();
        foreach($Produto_singular as $item){
            $x++;
            $existe = produto_mysql::where('prd_codigo',$item->PRD_CODIGO)->count();
            if($existe ==0){
                try{
                    $produtoMysql = new produto_mysql([
                        'prd_codigo'        => $item->PRD_CODIGO
                        ,'prd_descricao'    => $item->PRD_DESCRICAO
                        ,'um_codigo'        => $item->UM_CODIGO
                        ,'prd_tipo_produto' => $item->PRD_TIPO_PRODUTO
                        ,'ncm_codigo'       => $item->NCM_CODIGO
                    ]);
                    $produtoMysql->save();
                }catch(\Exception $e){
                    dd($e);
                }
            };
            print_r($x.'-'.$existe.' - '.$item->PRD_CODIGO.'-'.$item->PRD_DESCRICAO."\n");
        }

    }
}
