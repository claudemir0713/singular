<?php

namespace App\Http\Controllers\contabilidade;

use App\Helpers\Contabilidade;
use App\Helpers\Estoque;
use App\Helpers\Sped;
use App\Http\Controllers\Controller;
use App\Models\singular_estoque_bloco_k;
use App\Models\SPED_ESTOQUE_TEMP;
use Facade\FlareClient\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class contabilidadeController extends Controller
{
    public function exportaBaixas(Request $request)
    {
        $dateForm = $request->except('_token');
        return view('contabilidade.exportaBaixas',compact('dateForm'));
    }
    public function geraArquivoBiaxas(Request $request)
    {
        $dataI      = $request->dtI;
        $dataF      = $request->dtF;
        $tipo       = $request->tipo;
        if($tipo==0){
            $arquivo = 'Contas a Receber'.$dataI.'-'.$dataF.'.txt';
        }else{
            $arquivo = 'Contas a Pagar'.$dataI.'-'.$dataF.'.txt';
        };

        $baixa = Contabilidade::baixa($dataI,$dataF,$tipo);
        $txt = '';
        foreach($baixa as $item){
            $txt .=trim($item->TIPO).';';
            $txt .=trim(date('d/m/Y', strtotime($item->DATA))).';';
            $txt .=trim($item->DOCUMENTO).';';
            $txt .=trim($item->C).';';
            $txt .=trim($item->D).';';
            $txt .=trim($item->COD_HIST).';';
            $txt .=trim($item->DOCUMENTO).'|'.trim($item->HISTORICO).';';
            $txt .= number_format(trim($item->VALOR),2,',','')."\n";
        }
        $path = storage_path()."/app/contabilidade/$arquivo";
        $open = fopen($path,'w');
        fwrite($open,$txt);
        $fechar = fclose($open);
        // dd($fechar);
        return $fechar;
    }
    public function download($arq) {
        $path = "contabilidade/$arq";
        return response()->download(storage_path()."/app/contabilidade/$arq");

    }

    public function estoque(Request $request)
    {
        $dateForm = $request->except('_token');
        return view('contabilidade.estoque',compact('dateForm'));
    }

    public function sped(Request $request)
    {
        $dateForm = $request->except('_token');
        return view('contabilidade.sped',compact('dateForm'));
    }

    public function geraEstoque(Request $request){
        $ano  = $request->ano;
        $mes  = $request->mes;
        $estoque = Estoque::estoqueFinal($ano,$mes);

        return Response()->json('success');
    }

    public function importaAcertoEstoque(){
        return view('contabilidade.importaAcertoEstoque');
    }


    public function geraAcertoEstoque(Request $request)
    {
        $data = $request->texto;
        $texto = explode("\n",$data);
        $retorno = '';
        foreach($texto as $linhas){
            $linha      = explode("\t",$linhas);
            $id         = $linha[0];
            $pro_cod    = $linha[1];
            $pro_des    = $linha[2];
            $qtd        = floatval(str_replace(',','.',str_replace('.','',$linha[3])));
            $valor      = floatval(str_replace(',','.',str_replace('.','',$linha[4])));

            try{
                $atualiza = SPED_ESTOQUE_TEMP::find($id);
                $atualiza->SET_PRD_ESTOQUE = $qtd;
                $atualiza->SET_PRD_CUSTO_VALORIZACAO = $valor;
                $atualiza->save();
                $retorno.="OK -> Produto $pro_cod"."\n";
            }catch(\Exception $e){
                dd($e);
                $retorno.="Erro -> Produto $pro_cod"."\n";
            }
        }
        return response()->json(($retorno));

    }
    public function geraSped(Request $request){
        $dataI  = $request->dtI;
        $dataF  = $request->dtF;
        $arquivo = 'Sped'.$dataI.'-'.$dataF.'.txt';
        $cod_ver= '019';
        $cod_fin= $request->cod_fin;

        if($request->dtI){
            $Registro_0000  = Sped::Registro_0000($dataI,$dataF,$cod_ver,$cod_fin);
            $QTD_LIN_0000   = $Registro_0000['qtd'];
            $Registro_0000  = $Registro_0000['txt'];

            $Registro_0001  = Sped::Registro_0001();
            $QTD_LIN_0001   = $Registro_0001['qtd'];
            $Registro_0001  = $Registro_0001['txt'];

            $Registro_0002  = Sped::Registro_0002();
            $QTD_LIN_0002   = $Registro_0002['qtd'];
            $Registro_0002  = $Registro_0002['txt'];

            $Registro_0005  = Sped::Registro_0005();
            $QTD_LIN_0005   = $Registro_0005['qtd'];
            $Registro_0005  = $Registro_0005['txt'];

            $Registro_0100  = Sped::Registro_0100();
            $QTD_LIN_0100   = $Registro_0100['qtd'];
            $Registro_0100  = $Registro_0100['txt'];

            $Registro_0150  = Sped::Registro_0150($dataI,$dataF);
            $QTD_LIN_0150   = $Registro_0150['qtd'];
            $Registro_0150  = $Registro_0150['txt'];

            $Registro_0190  = Sped::Registro_0190($dataI,$dataF);
            $QTD_LIN_0190   = $Registro_0190['qtd'];
            $Registro_0190  = $Registro_0190['txt'];

            $Registro_0200  = Sped::Registro_0200($dataI,$dataF);
            $QTD_LIN_0200   = $Registro_0200['qtd'];
            $Registro_0200  = $Registro_0200['txt'];

            $Registro_0220  = Sped::Registro_0220($dataI,$dataF);
            $QTD_LIN_0220   = $Registro_0220['qtd'];
            $Registro_0220  = $Registro_0220['txt'];

            $QTD_LIN_0990   = 1+$QTD_LIN_0000+$QTD_LIN_0001+$QTD_LIN_0002+$QTD_LIN_0005+$QTD_LIN_0100+$QTD_LIN_0150+$QTD_LIN_0190+$QTD_LIN_0200+$QTD_LIN_0220;
            $Registro_0990  = Sped::Registro_0990($QTD_LIN_0990);
            $QTD_LIN_0990   = $Registro_0990['qtd'];
            $Registro_0990  = $Registro_0990['txt'];

            $Registro_B001  = Sped::Registro_B001();
            $QTD_LIN_B001   = $Registro_B001['qtd'];
            $Registro_B001  = $Registro_B001['txt'];

            $QTD_LIN_B990   = 1+$QTD_LIN_B001;
            $Registro_B990  = Sped::Registro_B990($QTD_LIN_B990);
            $QTD_LIN_B990   = $Registro_B990['qtd'];
            $Registro_B990  = $Registro_B990['txt'];

            /**************/
            $Registro_C001  = Sped::Registro_C001();
            $QTD_LIN_C001   = $Registro_C001['qtd'];
            $Registro_C001  = $Registro_C001['txt'];

            $QTD_LIN_C990   = 1+$QTD_LIN_C001;
            $Registro_C990  = Sped::Registro_C990($QTD_LIN_C990);
            $QTD_LIN_C990   = $Registro_C990['qtd'];
            $Registro_C990  = $Registro_C990['txt'];


            $Registro_D001  = Sped::Registro_D001();
            $QTD_LIN_D001   = $Registro_D001['qtd'];
            $Registro_D001  = $Registro_D001['txt'];

            $QTD_LIN_D990   = 1+$QTD_LIN_D001;
            $Registro_D990  = Sped::Registro_D990($QTD_LIN_D990);
            $QTD_LIN_D990   = $Registro_D990['qtd'];
            $Registro_D990  = $Registro_D990['txt'];


            $Registro_E001  = Sped::Registro_E001();
            $QTD_LIN_E001   = $Registro_E001['qtd'];
            $Registro_E001  = $Registro_E001['txt'];

            $QTD_LIN_E990   = 1+$QTD_LIN_E001;
            $Registro_E990  = Sped::Registro_E990($QTD_LIN_E990);
            $QTD_LIN_E990   = $Registro_E990['qtd'];
            $Registro_E990  = $Registro_E990['txt'];


            $Registro_G001  = Sped::Registro_G001();
            $QTD_LIN_G001   = $Registro_G001['qtd'];
            $Registro_G001  = $Registro_G001['txt'];

            $QTD_LIN_G990   = 1+$QTD_LIN_G001;
            $Registro_G990  = Sped::Registro_G990($QTD_LIN_G990);
            $QTD_LIN        = $Registro_G990['qtd'];
            $Registro_G990  = $Registro_G990['txt'];


            $Registro_H001  = Sped::Registro_H001();
            $QTD_LIN_H001   = $Registro_H001['qtd'];
            $Registro_H001  = $Registro_H001['txt'];

            $QTD_LIN_H990   = 1+$QTD_LIN_H001;
            $Registro_H990  = Sped::Registro_H990($QTD_LIN_H990);
            $QTD_LIN_H990   = $Registro_H990['qtd'];
            $Registro_H990  = $Registro_H990['txt'];


            $Registro_K001  = Sped::Registro_K001();
            $QTD_LIN_K001   = $Registro_K001['qtd'];
            $Registro_K001  = $Registro_K001['txt'];

            $Registro_K100  = Sped::Registro_K100($dataI,$dataF);
            $QTD_LIN_K100   = $Registro_K100['qtd'];
            $Registro_K100  = $Registro_K100['txt'];

            $Registro_K200  = Sped::Registro_K200($dataI,$dataF);
            $QTD_LIN_K200   = $Registro_K200['qtd'];
            $Registro_K200  = $Registro_K200['txt'];

            $QTD_LIN_K990   = $QTD_LIN_K001+$QTD_LIN_K200;
            $Registro_K990  = Sped::Registro_K990($QTD_LIN_K990);
            $QTD_LIN_K990   = $Registro_K990['qtd'];
            $Registro_K990  = $Registro_K990['txt'];


            $Registro_1001  = Sped::Registro_1001();
            $QTD_LIN_1001   = $Registro_1001['qtd'];
            $Registro_1001  = $Registro_1001['txt'];

            $QTD_LIN_1990   = 1+$QTD_LIN_1001;
            $Registro_1990  = Sped::Registro_1990($QTD_LIN_1990);
            $QTD_LIN_1990   = $Registro_1990['qtd'];
            $Registro_1990  = $Registro_1990['txt'];


            $Registro_9001  = Sped::Registro_9001();
            $QTD_LIN_9001   = $Registro_9001['qtd'];
            $Registro_9001  = $Registro_9001['txt'];

            $Registro_9900  = Sped::Registro_9900($QTD_LIN_0000,$QTD_LIN_0001,$QTD_LIN_0002,$QTD_LIN_0005,$QTD_LIN_0100,$QTD_LIN_0150,$QTD_LIN_0190,$QTD_LIN_0200,$QTD_LIN_0220,$QTD_LIN_0990,$QTD_LIN_B001,$QTD_LIN_C001,$QTD_LIN_D001,$QTD_LIN_E001,$QTD_LIN_G001,$QTD_LIN_H001,$QTD_LIN_K001,$QTD_LIN_K200,$QTD_LIN_1001,$QTD_LIN_1990-1);
            $QTD_LIN_9900   = $Registro_9900['qtd'];
            $Registro_9900  = $Registro_9900['txt'];


            $QTD_LIN_9900   = 1+$QTD_LIN_9900+$QTD_LIN_1990;
            $Registro_9990  = Sped::Registro_9990($QTD_LIN_9900);
            $QTD_LIN_9900   = $Registro_9990['qtd'];
            $Registro_9990  = $Registro_9990['txt'];

            $QTD_LIN_9999   = $QTD_LIN_0000+$QTD_LIN_0001+$QTD_LIN_0002+$QTD_LIN_0005+$QTD_LIN_0100+$QTD_LIN_0150+$QTD_LIN_0190+$QTD_LIN_0200+$QTD_LIN_0220+$QTD_LIN_0990+$QTD_LIN_B001+$QTD_LIN_B990+$QTD_LIN_C001+$QTD_LIN_C990+$QTD_LIN_D001+$QTD_LIN_D990+$QTD_LIN_E001+$QTD_LIN_E990+$QTD_LIN_H990+$QTD_LIN_K001+$QTD_LIN_K990+$QTD_LIN_1001+$QTD_LIN_1990+$QTD_LIN_9001+$QTD_LIN_9900;
            $Registro_9999  = Sped::Registro_9999($QTD_LIN_9999-3);
            $QTD_LIN_9999   = $Registro_9999['qtd'];
            $Registro_9999  = $Registro_9999['txt'];


            $sped  = $Registro_0000;
            $sped .= $Registro_0001;
            $sped .= $Registro_0002;
            $sped .= $Registro_0005;
            $sped .= $Registro_0100;
            $sped .= $Registro_0150;
            $sped .= $Registro_0190;
            $sped .= $Registro_0200;
            $sped .= $Registro_0220;
            $sped .= $Registro_0990;
            $sped .= $Registro_B001;
            $sped .= $Registro_B990;
            $sped .= $Registro_C001;
            $sped .= $Registro_C990;
            $sped .= $Registro_D001;
            $sped .= $Registro_D990;
            $sped .= $Registro_E001;
            $sped .= $Registro_E990;
            $sped .= $Registro_G001;
            $sped .= $Registro_G990;
            $sped .= $Registro_H001;
            $sped .= $Registro_H990;
            $sped .= $Registro_K001;
            $sped .= $Registro_K100;
            $sped .= $Registro_K200;
            $sped .= $Registro_K990;
            $sped .= $Registro_1001;
            $sped .= $Registro_1990;
            $sped .= $Registro_9001;
            $sped .= $Registro_9900;
            $sped .= $Registro_9990;
            $sped .= $Registro_9999;

            // dd($arquivo);

            $path = storage_path()."/app/contabilidade/$arquivo";
            $open = fopen($path,'w');
            fwrite($open,$sped);
            $fechar = fclose($open);
            return $fechar;
            // $path = "contabilidade/$arquivo";
            // return response()->download(storage_path()."/app/contabilidade/$arquivo");
        }

    }
}
