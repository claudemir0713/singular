<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UNIDADES_MEDIDA extends Model
{
    use HasFactory;
    protected $connection;
    function __construct()
    {
        return $this->connection = env('APP_NAME');
    }
	protected $table = 'UNIDADES_MEDIDA';
    protected $primaryKey = ['UM_CODIGO'];
    public $timestamps = false;

    protected $fillable= [
        'UM_CODIGO', 'UM_DESCRICAO', 'UM_CODIGO_NFSE', 'UM_PESADA'
    ];
}
