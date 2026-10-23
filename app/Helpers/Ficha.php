<?php
namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Ficha {
    public static function estrutura($cod_prod) {
        $sql = "SELECT * FROM P_ESTRUTURA_PRODUTO($cod_prod)";
        $estrutura = db::connection(env('APP_NAME'))->select($sql);
        return $estrutura;
    }
}
