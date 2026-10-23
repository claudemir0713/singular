<?php
namespace App\Helpers;

use App\Models\fluxo;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class Helper {

    public static function arrayPaginator($query, $request, $registrosPorPagina = 10) {
        $paginaAtual = $request->get('page', 1);
        $offset = ($paginaAtual * $registrosPorPagina) - $registrosPorPagina;

        return new LengthAwarePaginator(array_slice($query, $offset, $registrosPorPagina, true), count($query), $registrosPorPagina, $paginaAtual, ['path' => $request->url(), 'query' => $request->query()]);
    }

    public static function dataExtenso($date){
        $dia = date('d', strtotime($date));
        $mes = date('m', strtotime($date));
        $ano = date('Y', strtotime($date));

        if($mes==1){$mes = 'Janeiro';}
        elseif($mes==2){$mes = 'Fevereiro';}
        elseif($mes==3){$mes = 'Março';}
        elseif($mes==4){$mes = 'Abril';}
        elseif($mes==5){$mes = 'Maio';}
        elseif($mes==6){$mes = 'Junho';}
        elseif($mes==7){$mes = 'Julho';}
        elseif($mes==8){$mes = 'Agosto';}
        elseif($mes==9){$mes = 'Setembro';}
        elseif($mes==10){$mes = 'Outubro';}
        elseif($mes==11){$mes = 'Novembro';}
        elseif($mes==12){$mes = 'Dezembro';}

        $data = $dia.' de '.$mes.' de '.$ano;
        return $data;

    }

    public static function extenso($valor=0, $maiusculas=false) {
        // verifica se tem virgula decimal
        if (strpos($valor, ",") > 0) {
            $valor = str_replace(".", "", $valor);
            $valor = str_replace(",", ".", $valor);
        }

        $singular = array("centavo", "real", "mil", "milhão", "bilhão", "trilhão", "quatrilhão");
        $plural = array("centavos", "reais", "mil", "milhões", "bilhões", "trilhões","quatrilhões");

        $c = array("", "cem", "duzentos", "trezentos", "quatrocentos",
                "quinhentos", "seiscentos", "setecentos", "oitocentos", "novecentos");
        $d = array("", "dez", "vinte", "trinta", "quarenta", "cinquenta",
                "sessenta", "setenta", "oitenta", "noventa");
        $d10 = array("dez", "onze", "doze", "treze", "quatorze", "quinze",
                "dezesseis", "dezesete", "dezoito", "dezenove");
        $u = array("", "um", "dois", "três", "quatro", "cinco", "seis",
                "sete", "oito", "nove");

        $z = 0;

        $valor = number_format($valor, 2, ".", ".");
        $inteiro = explode(".", $valor);
        $cont = count($inteiro);
        for ($i = 0; $i < $cont; $i++)
                for ($ii = strlen($inteiro[$i]); $ii < 3; $ii++)
                $inteiro[$i] = "0" . $inteiro[$i];

        $fim = $cont - ($inteiro[$cont - 1] > 0 ? 1 : 2);
        $rt = '';
        for ($i = 0; $i < $cont; $i++) {
                $valor = $inteiro[$i];
                $rc = (($valor > 100) && ($valor < 200)) ? "cento" : $c[$valor[0]];
                $rd = ($valor[1] < 2) ? "" : $d[$valor[1]];
                $ru = ($valor > 0) ? (($valor[1] == 1) ? $d10[$valor[2]] : $u[$valor[2]]) : "";

                $r = $rc . (($rc && ($rd || $ru)) ? " e " : "") . $rd . (($rd &&
                        $ru) ? " e " : "") . $ru;
                $t = $cont - 1 - $i;
                $r .= $r ? " " . ($valor > 1 ? $plural[$t] : $singular[$t]) : "";
                if ($valor == "000"

                )$z++; elseif ($z > 0)
                $z--;
                if (($t == 1) && ($z > 0) && ($inteiro[0] > 0))
                $r .= ( ($z > 1) ? " de " : "") . $plural[$t];
                if ($r)
                $rt = $rt . ((($i > 0) && ($i <= $fim) &&
                        ($inteiro[0] > 0) && ($z < 1)) ? ( ($i < $fim) ? ", " : " e ") : " ") . $r;
        }

        if (!$maiusculas) {
                return($rt ? $rt : "zero");
        } elseif ($maiusculas == "2") {
                return (strtoupper($rt) ? strtoupper($rt) : "Zero");
        } else {
                return (ucwords($rt) ? ucwords($rt) : "Zero");
        }
    }
 //fim da função

    public static function formata_valor($valor) {
        $valor = preg_replace("/[^0-9\.|,|]/", "", $valor);
        $valor = str_replace(',','.',str_replace('.','',$valor));
        return $valor;
    }

    public static function inteiro($valor) {
        $valor = preg_replace("/[^0-9\.|,|]/", "", $valor);
        $valor = intval($valor);
        return $valor;
    }

}
