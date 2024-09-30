<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

     // Relação com OrcamentoGeral
    public function orcamento(): BelongsTo
    {
        return $this->belongsTo(OrcamentoGeral::class, 'id_orcamento'); // Certifique-se de que 'orcamento_id' é a chave estrangeira correta
    }
}
