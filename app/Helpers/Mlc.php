<?php
namespace App\Helpers;

use App\Models\mlc_hora;
use App\Models\mlc_horaprod;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Mlc {
    public static function horasInsert($ano,$mes,$CodCa,$CodConta,$valor) {
        try{
            $horas = new mlc_hora();
                $horas->Usu     = 'Root';
                $horas->Emp     = '371';
                $horas->Ano     = $ano;
                $horas->CodConta= $CodConta;
                $horas->CodCa   = $CodCa;
                $horas->PMed    = 0;
                $horas->PJan    = 0;
                $horas->PFev    = 0;
                $horas->PMar    = 0;
                $horas->PAbr    = 0;
                $horas->PMai    = 0;
                $horas->PJun    = 0;
                $horas->PJul    = 0;
                $horas->PAgo    = 0;
                $horas->PSet    = 0;
                $horas->POut    = 0;
                $horas->PNov    = 0;
                $horas->PDez    = 0;
                $horas->RMed    = 0;
                $horas->RJan    =($mes==1)? $valor : 0;
                $horas->RFev    =($mes==2)? $valor : 0;
                $horas->RMar    =($mes==3)? $valor : 0;
                $horas->RAbr    =($mes==4)? $valor : 0;
                $horas->RMai    =($mes==5)? $valor : 0;
                $horas->RJun    =($mes==6)? $valor : 0;
                $horas->RJul    =($mes==7)? $valor : 0;
                $horas->RAgo    =($mes==8)? $valor : 0;
                $horas->RSet    =($mes==9)? $valor : 0;
                $horas->ROut    =($mes==10)? $valor : 0;
                $horas->RNov    =($mes==11)? $valor : 0;
                $horas->RDez    =($mes==12)? $valor : 0;

            $horas->save();
        }catch(\Exception $e){
        }

    }
    public static function horasUpd($ano,$mes,$CodCa,$CodConta,$valor) {
        $horas =  mlc_hora::where('Ano',$ano)->where('CodConta',$CodConta)->where('CodCa',$CodCa)->first();
        switch ($mes) {
            case 1:
                $horas->RJan = $valor;
                break;
            case 2:
                $horas->RFev = $valor;
                break;
            case 3:
                $horas->RMar = $valor;
                break;
            case 4:
                $horas->RAbr = $valor;
                break;
            case 5:
                $horas->RMai = $valor;
                break;
            case 6:
                $horas->RJun = $valor;
                break;
            case 7:
                $horas->RJul = $valor;
                break;
            case 8:
                $horas->RAgo = $valor;
                break;
            case 9:
                $horas->RSet = $valor;
                break;
            case 10:
                $horas->ROut = $valor;
                break;
            case 11:
                $horas->RNov = $valor;
                break;
            case 12:
                $horas->RDez = $valor;
                break;
        }
        $horas->save();
    }
    public static function horasProdInsert($ano,$mes,$CodCa,$CodConta,$valor) {
        $horasProd = new mlc_horaprod();
            $horasProd->Usu     = 'Root';
            $horasProd->Emp     = '371';
            $horasProd->Ano     = $ano;
            $horasProd->CodConta= 1;
            $horasProd->CodCa   = $CodCa;
            $horasProd->PMed    = 0;
            $horasProd->PJan    = 0;
            $horasProd->PFev    = 0;
            $horasProd->PMar    = 0;
            $horasProd->PAbr    = 0;
            $horasProd->PMai    = 0;
            $horasProd->PJun    = 0;
            $horasProd->PJul    = 0;
            $horasProd->PAgo    = 0;
            $horasProd->PSet    = 0;
            $horasProd->POut    = 0;
            $horasProd->PNov    = 0;
            $horasProd->PDez    = 0;
            $horasProd->RMed    = 0;
            $horasProd->RJan    =($mes==1)? $valor : 0;
            $horasProd->RFev    =($mes==2)? $valor : 0;
            $horasProd->RMar    =($mes==3)? $valor : 0;
            $horasProd->RAbr    =($mes==4)? $valor : 0;
            $horasProd->RMai    =($mes==5)? $valor : 0;
            $horasProd->RJun    =($mes==6)? $valor : 0;
            $horasProd->RJul    =($mes==7)? $valor : 0;
            $horasProd->RAgo    =($mes==8)? $valor : 0;
            $horasProd->RSet    =($mes==9)? $valor : 0;
            $horasProd->ROut    =($mes==10)? $valor : 0;
            $horasProd->RNov    =($mes==11)? $valor : 0;
            $horasProd->RDez    =($mes==12)? $valor : 0;
        $horasProd->save();
    }
    public static function horasProdUpd($ano,$mes,$CodCa,$CodConta,$valor) {
        $horasProd =  mlc_horaprod::where('Ano',$ano)->where('CodCa',$CodCa)->first();
        switch ($mes) {
            case 1:
                $horasProd->RJan = $valor;
                break;
            case 2:
                $horasProd->RFev = $valor;
                break;
            case 3:
                $horasProd->RMar = $valor;
                break;
            case 4:
                $horasProd->RAbr = $valor;
                break;
            case 5:
                $horasProd->RMai = $valor;
                break;
            case 6:
                $horasProd->RJun = $valor;
                break;
            case 7:
                $horasProd->RJul = $valor;
                break;
            case 8:
                $horasProd->RAgo = $valor;
                break;
            case 9:
                $horasProd->RSet = $valor;
                break;
            case 10:
                $horasProd->ROut = $valor;
                break;
            case 11:
                $horasProd->RNov = $valor;
                break;
            case 12:
                $horasProd->RDez = $valor;
                break;
        }
        $horasProd->save();
    }
}
