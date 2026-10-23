<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class produto_custo_op_depara extends Model
{
    use HasFactory;

    protected $table = 'produto_custo_op_depara_mysql';

    protected $primaryKey = 'id';

    protected $fillable = [
        'cod_cc',
        'centro_custo',

        'cod_processo',
        'desc_processo',

        'cod_contahora',
        'contahora',

        'unid_med',

        'base_calculo',
        'cod_comp_aux',

        'ativo',
    ];

    protected $casts = [
        'cod_cc' => 'integer',
        'cod_contahora' => 'integer',
    ];
}
