<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class produto_ficha extends Model
{
    use HasFactory;

    protected $table = 'produto_ficha_mysql';

    protected $primaryKey = 'id';

    protected $fillable = [
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

        // Campos adicionados para cálculo de custo
        'tipo_custo',
        'tipo_mp',
        'custo_var',
        'custo_mp_unit',
        'valor_mp',
    ];
}
