<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DAV_SITUACOES_PERSONALIZADAS extends Model
{
    use HasFactory;
    protected $connection;
    function __construct()
    {
        return $this->connection = env('APP_NAME');
    }
    protected $table = 'DAV_SITUACOES_PERSONALIZADAS';
    protected $primaryKey = 'DSP_CODIGO';
    public $timestamps = false;
    protected $fillable=[
        'DSP_CODIGO', 'DSP_DESCRICAO', 'DSP_PADRAO', 'DSP_COR', 'DSP_COR_TEXTO', 'DSP_ORDEM'
    ];
}
