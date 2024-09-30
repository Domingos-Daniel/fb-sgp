<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    use HasFactory;

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

    public function patrocinio()
    {
        return $this->belongsTo(Patrocinio::class, 'id_patrocinio');
    }

    public function aprovador()
    {
        return $this->belongsTo(User::class, 'id_aprovador');
    }
}
