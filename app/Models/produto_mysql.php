<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class produto_mysql extends Model
{
    use HasFactory;
    // public $timestamps = false;
    protected $fillable= [
        'prd_codigo', 'prd_descricao', 'um_codigo', 'prd_tipo_produto', 'ncm_codigo'
    ];
    protected $primaryKey = 'id';
    protected $table = 'produto_mysql';
}
