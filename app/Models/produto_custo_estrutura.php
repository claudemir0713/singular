<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class produto_custo_estrutura extends Model
{
    use HasFactory;

    protected $table = 'produto_custo_estrutura_mysql';

    protected $primaryKey = 'id';

    protected $fillable = [
        'ano',
        'mes',
        'ano_mes',

        'cod',
        'descr',
        'grupo',

        'embalagem',
        'custo_percentual',

        'custo_mp',
        'custo_op',
        'custo_total',

        'origem',
    ];

    protected $casts = [
        'ano' => 'integer',
        'mes' => 'integer',

        'custo_percentual' => 'decimal:6',

        'custo_mp' => 'decimal:6',
        'custo_op' => 'decimal:6',
        'custo_total' => 'decimal:6',
    ];
}
