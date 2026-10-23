<?php
namespace App\Helpers;

use App\Models\DAV;
use App\Models\DAV_CARREGAMENTO_PEDIDOS;
use App\Models\NF_VENDA_CABECALHO;
use App\Models\papeleta;
use App\Models\papeleta_dav;
use App\Models\papeleta_parcela;

class Papeletas {
    public static function gravaCargaMysql($nr_carga) {
        $papeletaExiste = papeleta::where('carga',$nr_carga)->exists();
        if($papeletaExiste==false){
            try{
                $papeleta = new papeleta([
                    'carga'     => $nr_carga
                    , 'data'    => date('Y-m-d')
                ]);
                $papeleta->save();
                $papeleta_id = $papeleta->id;

                $davs = Papeletas::carga($nr_carga);
                foreach($davs as $item_dav){
                    try{
                        $DAV_NUMERO_AUXILIAR = $item_dav->DAV_NUMERO_AUXILIAR;
                        $papeleta_dav = new papeleta_dav([
                            'papeleta_id'   => $papeleta_id
                            , 'carga'       => $nr_carga
                            , 'dav'         => $DAV_NUMERO_AUXILIAR
                            , 'cliente'     => $item_dav->ENT_NOME
                            , 'data'        =>  date('Y-m-d')
                            , 'cod_vend'    => $item_dav->COD_VEND
                            , 'vendedor'    => $item_dav->VENDEDOR
                        ]);
                        $papeleta_dav->save();
                        $papeleta_dav_id = $papeleta_dav->id;
                        Papeletas::pd($papeleta_id, $papeleta_dav_id,$nr_carga,$DAV_NUMERO_AUXILIAR);
                        Papeletas::nf($papeleta_id, $papeleta_dav_id,$nr_carga,$DAV_NUMERO_AUXILIAR);
                    }catch(\Exception $e){
                        papeleta::where('carga',$nr_carga)->delete();
                        papeleta_dav::where('carga',$nr_carga)->delete();
                        return response()->json([
                            'success'   => false,
                            'tabela'    => 'papeleta_dav' ,
                            'title'     => 'Error',
                            'type'      => 'error',
                            'html'      =>  $e->getMessage(),
                            'timer'     => 5000
                        ], 200);
                    }
                }
            }catch(\Exception $e){
                papeleta::where('carga',$nr_carga)->delete();
                papeleta_dav::where('carga',$nr_carga)->delete();
                return response()->json([
                    'success'   => false,
                    'tabela'    => 'Papeletas' ,
                    'title'     => 'Error',
                    'type'      => 'error',
                    'html'      =>  $e->getMessage(),
                    'timer'     => 5000
                ], 200);
            }
            return response()->json([
                'success'   => true,
                'tabela'    => 'Papeletas' ,
                'title'     => 'Success',
                'type'      => 'success',
                'html'      => 'Processo concluído!',
                'timer'     => 2000
            ], 200);
        }
    }
    public static function carga($nr_carga) {
        $carga_dav = DAV_CARREGAMENTO_PEDIDOS::where('DCA_CODIGO',$nr_carga)->orderBy('DCA_ORDEM_ENTREGA')->get();
        $listaDav = $carga_dav->pluck('DAV_NUMERO')->toArray();
        $dav = DAV::leftJoin('DAV_SITUACOES_PERSONALIZADAS','DAV_SITUACOES_PERSONALIZADAS.DSP_CODIGO','DAV.DSP_CODIGO')
                    ->leftJoin('ENTIDADES', function($join){
                        $join->on('ENTIDADES.ENT_CODIGO', '=', 'DAV.ENT_CODIGO')
                            ->where('ENTIDADES.ENT_TIPO', '=', 1);
                    })
                    ->leftJoin('PARTICIPANTE as REP','REP.PART_REPRESENTANTE_CODIGO','DAV.PART_REPRESENTANTE_CODIGO')
                    -> whereIN('DAV_NUMERO',$listaDav)
                    ->orderBy('DAV_NUMERO_AUXILIAR')
                    ->get([
                        'DAV_NUMERO_AUXILIAR'
                        ,'DSP_DESCRICAO'
                        ,'ENT_NOME'
                        ,'DAV.PART_REPRESENTANTE_CODIGO AS COD_VEND'
                        ,'REP.PART_NOME AS VENDEDOR'
                    ]);
        return $dav;
    }
    public static function pd($papeleta_id, $papeleta_dav_id,$nr_carga,$DAV_NUMERO_AUXILIAR) {
        $parcelas  = DAV::Join('FIN_CONTAS','FIN_CONTAS.DOC_NUMERO_ORIGEM','DAV.DOC_NUMERO')
                        ->leftJoin('DAV_SITUACOES_PERSONALIZADAS','DAV_SITUACOES_PERSONALIZADAS.DSP_CODIGO','DAV.DSP_CODIGO')
                        ->where('DAV.DAV_NUMERO_AUXILIAR',$DAV_NUMERO_AUXILIAR)
                        ->orderBy('FIN_CONTAS.CON_SEQUENCIA')
                        ->groupBy([
                            'FIN_CONTAS.CON_CODIGO'
                            , 'FIN_CONTAS.CON_NUMERO'
                            , 'FIN_CONTAS.CON_SEQUENCIA'
                            , 'FIN_CONTAS.CON_DT_INCLUSAO'
                            , 'FIN_CONTAS.CON_DT_VENCIMENTO'
                            , 'FIN_CONTAS.ENT_CODIGO'
                            , 'FIN_CONTAS.ENT_TIPO'
                            , 'FIN_CONTAS.USU_COD'
                            , 'FIN_CONTAS.CON_VALOR_ORIGINAL'
                            , 'DSP_DESCRICAO'
                            , 'DAV.DAV_NUMERO_AUXILIAR'
                        ])
                        ->get([
                            'FIN_CONTAS.CON_CODIGO'
                            , 'FIN_CONTAS.CON_NUMERO'
                            , 'FIN_CONTAS.CON_SEQUENCIA'
                            , 'FIN_CONTAS.CON_DT_INCLUSAO'
                            , 'FIN_CONTAS.CON_DT_VENCIMENTO'
                            , 'FIN_CONTAS.ENT_CODIGO'
                            , 'FIN_CONTAS.ENT_TIPO'
                            , 'FIN_CONTAS.USU_COD'
                            , 'FIN_CONTAS.CON_VALOR_ORIGINAL'
                            , 'DSP_DESCRICAO'
                            , 'DAV.DAV_NUMERO_AUXILIAR'
                        ]);
        if($parcelas){
            foreach($parcelas as $item){
                try{
                    $papeleta_parcela = new papeleta_parcela([
                        'papeleta_id'       =>$papeleta_id
                        , 'papeleta_dav_id' =>$papeleta_dav_id
                        , 'considera'       =>'S'
                        , 'carga'           =>$nr_carga
                        , 'dav'             =>$DAV_NUMERO_AUXILIAR
                        , 'parcela'         =>$item->CON_SEQUENCIA
                        , 'tipo'            =>'PD'
                        , 'vencimento'      =>$item->CON_DT_VENCIMENTO
                        , 'valor'           =>$item->CON_VALOR_ORIGINAL
                    ]);
                    $papeleta_parcela->save();
                }catch(\Exception $e){
                    papeleta::where('carga',$nr_carga)->delete();
                    papeleta_dav::where('carga',$nr_carga)->delete();
                    papeleta_parcela::where('carga',$nr_carga)->delete();
                    return response()->json([
                        'success'   => false,
                        'tabela'    => 'papeleta_parcela_pd' ,
                        'title'     => 'Error',
                        'type'      => 'error',
                        'html'      =>  $e->getMessage(),
                        'timer'     => 5000
                    ], 200);
                }
            }
        }
    }
    public static function nf($papeleta_id, $papeleta_dav_id,$nr_carga,$DAV_NUMERO_AUXILIAR) {
        $parcelas  = NF_VENDA_CABECALHO::leftJoin('NF_VENDA_ITENS','NF_VENDA_ITENS.NF_NUMERO','NF_VENDA_CABECALHO.NF_NUMERO')
                        ->leftJoin('DAV','DAV.DAV_NUMERO','NF_VENDA_ITENS.PED_NUMERO')
                        ->leftJoin('DAV_SITUACOES_PERSONALIZADAS','DAV_SITUACOES_PERSONALIZADAS.DSP_CODIGO','DAV.DSP_CODIGO')
                        ->Join('FIN_CONTAS','FIN_CONTAS.DOC_NUMERO_ORIGEM','NF_VENDA_CABECALHO.DOC_NUMERO')
                        ->where('DAV.DAV_NUMERO_AUXILIAR',$DAV_NUMERO_AUXILIAR)
                        ->orderBy('FIN_CONTAS.CON_SEQUENCIA')
                        ->groupBy([
                            'FIN_CONTAS.CON_CODIGO'
                            , 'FIN_CONTAS.CON_NUMERO'
                            , 'FIN_CONTAS.CON_SEQUENCIA'
                            , 'FIN_CONTAS.CON_DT_INCLUSAO'
                            , 'FIN_CONTAS.CON_DT_VENCIMENTO'
                            , 'FIN_CONTAS.ENT_CODIGO'
                            , 'FIN_CONTAS.ENT_TIPO'
                            , 'FIN_CONTAS.USU_COD'
                            , 'FIN_CONTAS.CON_VALOR_ORIGINAL'
                            , 'DSP_DESCRICAO'
                            , 'DAV.DAV_NUMERO_AUXILIAR'
                        ])
                        ->get([
                            'FIN_CONTAS.CON_CODIGO'
                            , 'FIN_CONTAS.CON_NUMERO'
                            , 'FIN_CONTAS.CON_SEQUENCIA'
                            , 'FIN_CONTAS.CON_DT_INCLUSAO'
                            , 'FIN_CONTAS.CON_DT_VENCIMENTO'
                            , 'FIN_CONTAS.ENT_CODIGO'
                            , 'FIN_CONTAS.ENT_TIPO'
                            , 'FIN_CONTAS.USU_COD'
                            , 'FIN_CONTAS.CON_VALOR_ORIGINAL'
                            , 'DSP_DESCRICAO'
                            , 'DAV.DAV_NUMERO_AUXILIAR'
                        ]);
        if($parcelas){
            foreach($parcelas as $item){
                try{
                    $papeleta_parcela = new papeleta_parcela([
                        'papeleta_id'       =>$papeleta_id
                        , 'papeleta_dav_id' =>$papeleta_dav_id
                        , 'considera'       =>'N'
                        , 'carga'           =>$nr_carga
                        , 'dav'             =>$DAV_NUMERO_AUXILIAR
                        , 'parcela'         =>$item->CON_SEQUENCIA
                        , 'tipo'            =>'NF'
                        , 'vencimento'      =>$item->CON_DT_VENCIMENTO
                        , 'valor'           =>$item->CON_VALOR_ORIGINAL
                    ]);
                    $papeleta_parcela->save();
                }catch(\Exception $e){
                    papeleta::where('carga',$nr_carga)->delete();
                    papeleta_dav::where('carga',$nr_carga)->delete();
                    papeleta_parcela::where('carga',$nr_carga)->delete();
                    return response()->json([
                        'success'   => false,
                        'tabela'    => 'papeleta_parcela_nf' ,
                        'title'     => 'Error',
                        'type'      => 'error',
                        'html'      =>  $e->getMessage(),
                        'timer'     => 5000
                    ], 200);
                }
            }
        }
    }

    public static function dadosMySql($nr_carga, $vendedor = null)
    {
        $dados_dav = [];
        $dados_parcela = [];

        $papeleta = papeleta::where('carga', $nr_carga)->first();

        $papeleta_dav = papeleta_dav::where('carga', $nr_carga);

        if (!empty($vendedor)) {
            $papeleta_dav->where('vendedor', 'like', '%' . $vendedor . '%');
        }

        $papeleta_dav = $papeleta_dav->get();

        $papeleta_parcela = papeleta_parcela::where('carga', $nr_carga)->get();

        foreach ($papeleta_dav as $item) {
            $dados_dav[] = [
                'cliente'      => $item->cliente,
                'frete_valor'  => number_format($item->frete_valor, 2, ',', '.'),
                'cobranca'     => $item->cobranca,
                'dav'          => $item->dav,
                'dav_id'       => $item->id,
                'vendedor'     => $item->vendedor
            ];
        }

        foreach ($papeleta_parcela as $item) {
            $dados_parcela[] = [
                'dav_parcela_id' => $item->id,
                'dav'            => $item->dav,
                'parcela'        => $item->parcela,
                'tipo'           => $item->tipo,
                'considera'      => $item->considera,
                'vencimento'     => $item->vencimento,
                'valor'          => $item->valor,
                'cobranca'       => $item->cobranca,
                'obs'            => $item->obs,
            ];
        }

        return response()->json([
            'papeleta_id'    => $papeleta->id ?? null,
            'carga'          => $papeleta->carga ?? null,
            'data'           => $papeleta->data ?? null,
            'placa'          => $papeleta->placa ?? null,
            'motorista'      => $papeleta->motorista ?? null,
            'total'          => $papeleta->total ?? null,
            'dados_dav'      => $dados_dav,
            'dados_parcela'  => $dados_parcela,
        ]);
    }

}
