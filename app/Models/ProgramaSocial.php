<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProgramaSocial extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'programa_socials';

    protected $fillable = [
        'id_criador', 
        'titulo', 
        'descricao', 
        'publico_alvo', 
        'meta',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['id_criador', 'titulo', 'descricao', 'publico_alvo', 'meta']);
        // Chain fluent methods for configuration options
    }

    public function subprogramas()
    {
        return $this->hasMany(Subprograma::class, 'id_programa');
    }

    public function orcamentoPrograma()
    {
        return $this->hasOne(OrcamentoPrograma::class, 'id_programa');
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
