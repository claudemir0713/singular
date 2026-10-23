<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class produto_embalagem extends Model
{
    protected $table = 'produto_embalagem_mysql';

    protected $primaryKey = 'id';

    protected $fillable = [
        'cod_prod',
        'des_prod',
        'embalagem',
        'custo_percentual',
        'origem',
    ];

    protected $casts = [
        'custo_percentual' => 'decimal:6',
    ];
}
