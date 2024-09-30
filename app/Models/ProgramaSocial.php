<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramaSocial extends Model
{
    use HasFactory;

    protected $table = 'programa_socials';

    protected $fillable = [
        'id_criador', 
        'titulo', 
        'descricao', 
        'publico_alvo', 
        'meta',
        'orcamento',
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
