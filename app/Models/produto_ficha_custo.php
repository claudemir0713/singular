<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class produto_ficha_custo extends Model
{
    use HasFactory;

    protected $table = 'produto_ficha_custo_mysql';

    protected $primaryKey = 'id';

    protected $fillable = [
        'ano',
        'mes',
        'ano_mes',

        'cod',
        'descr',
        'grupo',

        'cod_pai',
        'desc_pai',

        'cod_comp',
        'desc_comp',
        'tipo_comp',
        'grupo_comp',

        'qtde_calc',
        'nivel',

        'tipo_custo',
        'tipo_mp',

        'custo_var',

        'base_m2',
        'base_m3',
        'base_kg',
        'base_op_qtd',
        'base_op_origem',

        'custo_mp_unit',
        'valor_mp',

        'custo_op_unit',
        'valor_op',

        'origem',
    ];

    protected $casts = [
        'ano' => 'integer',
        'mes' => 'integer',
        'nivel' => 'integer',

        'qtde_calc' => 'decimal:6',
        'custo_var' => 'decimal:6',

        'base_m2' => 'decimal:6',
        'base_m3' => 'decimal:6',
        'base_kg' => 'decimal:6',
        'base_op_qtd' => 'decimal:6',

        'custo_mp_unit' => 'decimal:6',
        'valor_mp' => 'decimal:6',

        'custo_op_unit' => 'decimal:6',
        'valor_op' => 'decimal:6',
    ];


}
