<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class singular_estoque_bloco_k extends Model
{
    use HasFactory;
    // public $timestamps = false;
    protected $fillable= [
        'id'
        , 'data'
        , 'prd_codigo'
        , 'prd_descri'
        , 'qtd'
        , 'valor'

    ];
    protected $primaryKey = 'id';
    protected $table = 'singular_estoque_bloco_k';
}
