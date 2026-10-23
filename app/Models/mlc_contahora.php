<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mlc_contahora extends Model
{
    protected $connection = 'mlc';

    protected $table = 'contahora';

    protected $primaryKey = 'N';

    public $timestamps = false;

    protected $fillable = [
        'Cod',
        'ContaHora',
        'Sinal',
    ];

    protected $casts = [
        'N' => 'integer',
        'Cod' => 'integer',
    ];
}
