<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CFOP_GRUPOS extends Model
{
    use HasFactory;
    protected $connection;
    function __construct()
    {
        return $this->connection = env('APP_NAME');
    }
	protected $table = 'CFOP_GRUPOS';
    protected $primaryKey = 'CFGR_CODIGO';
    public $timestamps = false;
    protected $fillable= [
        'CFGR_CODIGO',
        ' CFGR_DESCRICAO',
        ' CFGR_DESCRICAO_NF',
        ' CFGR_TIPO',
        ' CFGR_PADRAO',
        ' CFGR_ATIVO'
	];
}
