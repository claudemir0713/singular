<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class papeleta_parcela extends Model
{
    use HasFactory;
    protected $fillable= [
        'id', 'papeleta_id', 'papeleta_dav_id', 'considera', 'carga', 'dav', 'parcela', 'tipo', 'vencimento', 'valor', 'cobranca', 'obs'
    ];
    protected $primaryKey = 'id';
    protected $table = 'papeleta_parcela';
}
