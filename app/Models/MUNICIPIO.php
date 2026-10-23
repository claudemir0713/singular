<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MUNICIPIO extends Model
{
    use HasFactory;
    protected $connection;
    function __construct()
    {
        return $this->connection = env('APP_NAME');
    }
	protected $table = 'MUNICIPIO';
    protected $primaryKey = 'MUN_CODIGO';
    public $timestamps = false;
    protected $fillable= [
        'MUN_CODIGO',
        ' MUN_NOME',
        ' UF_CODIGO',
        ' MUN_CODIGO_RECEITA_FEDERAL',
        ' MUN_NOME_IBGE',
        ' MUN_LINK_NFSE',
        ' PNFSE_CODIGO'
	];
}
