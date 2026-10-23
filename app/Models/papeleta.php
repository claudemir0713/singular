<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class papeleta extends Model
{
    use HasFactory;
    protected $fillable= [
        'id', 'carga', 'data', 'total', 'placa', 'motorista'
    ];
    protected $primaryKey = 'id';
    protected $table = 'papeleta';
}
