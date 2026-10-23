<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmpresaParametro extends Model
{
    protected $connection = 'mysql';

    protected $table = 'empresa_parametro';

    protected $fillable = [
        'empresa_id',
        'margem',
        'taxa_financeira',
        'icms',
        'simples',
        'pis',
        'cofins',
        'ir_csll',
        'comissao',
        'difal',
        'frete',
        'despesa_fixa',
        'outros',
    ];
}
