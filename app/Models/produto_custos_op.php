<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class produto_custos_op extends Model
{
    use HasFactory;

    protected $table = 'produto_custos_op_mysql';

    protected $primaryKey = 'id';

    protected $fillable = [
        'ano',
        'mes',
        'ano_mes',

        'ano_base',
        'mes_base',
        'ano_mes_base',

        'cod_cc',
        'centro_custo',

        'cod_processo',
        'desc_processo',

        'cod_contahora',
        'contahora',

        'unid_med',

        'base_qtd',

        'despesa_direta',
        'despesa_alocada',
        'despesas',

        'custo_op_unit',

        'origem',
    ];

    protected $casts = [
        'ano' => 'integer',
        'mes' => 'integer',

        'ano_base' => 'integer',
        'mes_base' => 'integer',

        'cod_cc' => 'integer',
        'cod_contahora' => 'integer',

        'base_qtd' => 'decimal:6',

        'despesa_direta' => 'decimal:6',
        'despesa_alocada' => 'decimal:6',
        'despesas' => 'decimal:6',

        'custo_op_unit' => 'decimal:6',
    ];
}
