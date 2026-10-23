<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SPED_ESTOQUE_TEMP extends Model
{
    use HasFactory;
    protected $connection;
    function __construct()
    {
        return $this->connection = env('APP_NAME');
    }
	protected $table = 'SPED_ESTOQUE_TEMP';
    protected $primaryKey = 'SET_CODIGO';
    public $timestamps = false;
    protected $fillable= [
        'SET_PERIODO'
        , 'PRD_CODIGO'
        , 'PRD_DESCRICAO'
        , 'SET_PRD_ESTOQUE'
        , 'SET_PRD_CUSTO_VALORIZACAO'
        , 'SET_CODIGO'
    ];
}
