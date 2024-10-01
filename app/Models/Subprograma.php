<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Subprograma extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'id_programa', 
        'descricao', 
        'valor', 
        'id_criador',
        'tipo_pagamento',
        'duracao_patrocinio',
        'regras_especificas',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['id_programa', 'descricao', 'valor', 'id_criador', 'tipo_pagamento', 'duracao_patrocinio', 'regras_especificas']);
        // Chain fluent methods for configuration options
    }

    public function programaSocial()
    {
        return $this->belongsTo(ProgramaSocial::class, 'id_programa');
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'id_criador');
    }

    public function programa()
    {
        return $this->belongsTo(ProgramaSocial::class, 'id_programa');
    }

    public function patrocinios()
    {
        return $this->hasMany(Patrocinio::class, 'id_subprograma');
    }
}
