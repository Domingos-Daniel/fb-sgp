<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflowable_type', 
        'workflowable_id', 
        'status', 
        'aprovador_id', 
        'data_aprovacao', 
        'observacoes',
        'etapa' // Etapa do workflow
    ];

    public function aprovador_1()
{
    return $this->belongsTo(User::class, 'aprovador_1_id');
}

public function aprovador_2()
{
    return $this->belongsTo(User::class, 'aprovador_2_id');
}


    public function workflowable()
    {
        return $this->morphTo();
    }

    public function aprovador()
    {
        return $this->belongsTo(User::class, 'aprovador_id');
    }
}
