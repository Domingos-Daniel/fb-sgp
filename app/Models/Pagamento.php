<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Pagamento extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'id_patrocinio',
        'data_pagamento',
        'valor',
        'status',
        'data_aprovacao',
        'motivo_rejeicao',
        'id_aprovador',
        'observacoes',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['id_patrocinio', 'data_pagamento', 'valor', 'status', 'data_aprovacao', 'motivo_rejeicao', 'id_aprovador', 'observacoes']);
        // Chain fluent methods for configuration options
    }

    public function patrocinio()
    {
        return $this->belongsTo(Patrocinio::class, 'id_patrocinio');
    }

    public function aprovador()
    {
        return $this->belongsTo(User::class, 'id_aprovador');
    }
}
