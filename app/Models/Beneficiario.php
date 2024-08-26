<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Beneficiario extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'nome',
        'bi',
        'nif',
        'data_nascimento',
        'genero',
        'email',
        'tipo_beneficiario',
        'telemovel',
        'telemovel_alternativo',
        'endereco',
        'pais',
        'coordenadas_bancarias',
        'ano_frequencia',
        'curso',
        'universidade_ou_escola',
        'observacoes',
        'id_criador',
    ];

    public function criador()
    {
        return $this->belongsTo(User::class, 'id_criador');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['name', 'bi', 'nif', 'data_nascimento', 'genero', 'email', 'telemovel', 'telemovel_alternativo', 'endereco', 'pais', 'coordenadas_bancarias', 'ano_frequencia', 'curso', 'universidade_ou_escola', 'observacoes']);
        // Chain fluent methods for configuration options
    }
}
