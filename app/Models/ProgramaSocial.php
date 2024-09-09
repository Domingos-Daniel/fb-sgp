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
        'orcamento',
        'id_criador',
    ];

    public function subprogramas()
    {
        return $this->hasMany(Subprograma::class);
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'id_criador');
    }
}
