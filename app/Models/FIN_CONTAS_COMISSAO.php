<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FIN_CONTAS_COMISSAO extends Model
{
    use HasFactory;
    protected $connection;
    function __construct()
    {
        return $this->connection = env('APP_NAME');
    }
    protected $table = 'FIN_CONTAS_COMISSAO';
    protected $primaryKey = 'CONC_CODIGO';
    public $timestamps = false;
    protected $fillable= [
        'CONC_CODIGO', 'CON_CODIGO', 'USU_COD_VENDEDOR', 'PART_REPRESENTANTE_CODIGO', 'CONC_PERC_COMISSAO'
	];

}
