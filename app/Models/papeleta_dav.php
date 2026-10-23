<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class papeleta_dav extends Model
{
    use HasFactory;
    protected $fillable= [
        'id', 'papeleta_id', 'carga', 'dav', 'cliente', 'data', 'frete_valor', 'cobranca','cod_vend','vendedor'
    ];
    protected $primaryKey = 'id';
    protected $table = 'papeleta_dav';
}
