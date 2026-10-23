<?php

namespace App\Jobs;

use App\Models\PLANNER_ESTOQUE_BLOCO_K;
use App\Models\sigular_estoque_bloco_k;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class importaSaldoIEstoque implements ShouldQueue
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
                        LTRIM(RTRIM(SB9.B9_CODPROD)) 	 AS COD_PROD
                        ,LTRIM(RTRIM(SB1.B1_DESCRI)) AS DESC_PROD
                        ,SUM(SB9.B9_QINI)			 AS QTD_EI
                        ,SUM(SB9.B9_VINI1)			 AS TOT_EI
                        ,CONVERT(DATE, SB9.B9_DATA) AS DT_FECHAMENTO
                            FROM SB9
                        LEFT JOIN SB1 ON SB1.B1_CODPROD = SB9.B9_CODPROD
                                    AND LEFT(SB1.B1_FILIAL,2) = LEFT(SB9.B9_FILIAL,2)
                                    AND SB1.D_E_L_E_T_ <> '*'

                    WHERE SB9.D_E_L_E_T_ <> '*'
                    AND sb1.B1_TIPO IN ('2','3')
                    AND SB9.B9_LOCAL = '01'
                    AND YEAR(SB9.B9_DATA) = 2025
                    AND MONTH(SB9.B9_DATA) = 2
                    AND DAY(SB9.B9_DATA) = (SELECT DAY(MAX(SB91.B9_DATA))
                                            FROM SB9 SB91
                                            WHERE YEAR(SB91.B9_DATA) = YEAR(SB9.B9_DATA) AND MONTH(SB91.B9_DATA) = MONTH(SB9.B9_DATA)
                                            )
                    GROUP BY SB9.B9_CODPROD
                            ,SB1.B1_DESCRI
                            ,SB9.B9_DATA

        ";
        $est = DB::connection('totvs')->select($sql);
        $x = 0;
        $BLOCO_K = PLANNER_ESTOQUE_BLOCO_K::first();
        $BLOCO_K->PRD_CODIGO = 1;
        $BLOCO_K->DESC_PROD = 'teste';
        $BLOCO_K->QTD = '2';
        $BLOCO_K->VALOR = '3';
        $BLOCO_K->DATA = date('Y-m-d');
        $BLOCO_K->save();
        dd($BLOCO_K);
        foreach($est as $item){
            $x++;
            try{
                $estoque = new PLANNER_ESTOQUE_BLOCO_K([
                    'PRD_CODIGO'  => $item->COD_PROD
                    , 'PRD_DESCRI'  => $item->DESC_PROD
                    , 'QTD'         => number_format($item->QTD_EI,2,'.','')
                    , 'VALOR'       => number_format($item->TOT_EI,2,'.','')
                    , 'DATA'        => $item->DT_FECHAMENTO
                ]);
                dd($estoque);
                // $estoque->save();
            }catch(\Exception $e){
                dd($e);
            }
            print_r($x.'-'.$item->COD_PROD.'-'.number_format($item->QTD_EI,2,'.','').'-'.number_format($item->TOT_EI,2,'.','')."\n");
        }

    }
}
