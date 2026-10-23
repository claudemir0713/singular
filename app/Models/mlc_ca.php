<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class mlc_ca extends Model
{
    protected $connection = 'mlc';

    protected $table = 'ca';

    protected $primaryKey = 'N';

    public $timestamps = false;

    protected $fillable = [
        'Emp',
        'Cod',
        'Ca',
        'Fam',
        'Gru',
        'Sgr',
        'Hora',
        'Soma',
        'NomeCH',
        'NomeHD',
        'NomeHP',
        'NomeInv',
        'ListarCM',
    ];

    protected $casts = [
        'N' => 'integer',
        'Emp' => 'integer',
        'Cod' => 'integer',
        'Fam' => 'integer',
        'Gru' => 'integer',
        'Sgr' => 'integer',
        'Hora' => 'integer',
        'Soma' => 'integer',
        'ListarCM' => 'integer',
    ];
}
