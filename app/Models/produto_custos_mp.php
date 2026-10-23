<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class produto_custos_mp extends Model
{
    use HasFactory;

    protected $table = 'produto_custos_mp_mysql';

    protected $primaryKey = 'id';

    protected $fillable = [
        'ano',
        'mes',
        'ano_mes',

        'cod_mp',
        'desc_mp',
        'unid_med',
        'tipo',

        'unit_manual',
        'unit_sistema',
        'diferenca',

        'valor_unit',
        'credito_imp',
        'custo_liquido',
        'custo_ok',
        'utilizada_estrutura',

        'origem',
    ];

    protected $casts = [
        'ano' => 'integer',
        'mes' => 'integer',

        'unit_manual' => 'decimal:6',
        'unit_sistema' => 'decimal:6',
        'diferenca' => 'decimal:6',

        'valor_unit' => 'decimal:6',
        'credito_imp' => 'decimal:6',
        'custo_liquido' => 'decimal:6',
        'custo_ok' => 'decimal:6',
    ];
}
