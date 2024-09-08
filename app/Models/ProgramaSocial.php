<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramaSocial extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_criador', 
        'titulo', 
        'descricao', 
        'publico_alvo', 
        'meta',
        'id_criador',
    ];
}
