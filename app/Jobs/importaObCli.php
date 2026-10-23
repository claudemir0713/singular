<?php

namespace App\Jobs;

use App\Models\ENTIDADES;
use App\Models\FIN_CONTAS;
use App\Models\PARTICIPANTE;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class importaObCli implements ShouldQueue
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
                COD_PESSOA
                ,REPLACE(REPLACE(OBSERVACAO,CHAR(13) + Char(10) ,' '), CHAR(10), '') as A1_OBSERV
            FROM (
            SELECT
                CASE
                            WHEN A1_PESSOA = '00001' THEN '06242'
                            WHEN A1_PESSOA = '00004' THEN '06243'
                            WHEN A1_PESSOA = '00005' THEN '06244'
                            WHEN A1_PESSOA = '00006' THEN '06245'
                            WHEN A1_PESSOA = '00007' THEN '06246'
                            ELSE A1_PESSOA
                        END 										AS COD_PESSOA
                        ,LTRIM(RTRIM(CONVERT(VARCHAR(MAX),CONVERT(VARBINARY(MAX),A1_OBSERV),0))) AS OBSERVACAO
                        ,A1_OBSERV

            FROM SA1

            WHERE D_E_L_E_T_ <>'*'
            AND A1_PESSOA NOT IN ('06242','06243','06244','06245','06246')
            AND A1_CLIENTE = 'S'
            ) DADOS
        ";
        $cli = DB::connection('totvs')->select($sql);
        $x = 0;
        foreach($cli as $item){
            $x++;
            $obs =$item->A1_OBSERV;
            $cod = ltrim($item->COD_PESSOA,0);
            try{
                if($obs){
                    $altera = PARTICIPANTE::where('PART_CLIENTE_CODIGO',$cod)->first();
                    $altera->PART_OBSERVACOES_CREDITO = $obs;
                    $altera->save();
                };
            }catch(\Exception $e){
                dd($cod,$obs);
            }
            print_r($x.'-'.$cod.' - '.$obs."\n");
        }

    }
}
