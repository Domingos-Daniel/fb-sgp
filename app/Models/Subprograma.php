<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subprograma extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_programa', 
        'descricao', 
        'valor', 
        'id_criador'
    ];

    public function programaSocial()
    {
        return $this->belongsTo(ProgramaSocial::class, 'id_programa');
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'id_criador');
    }
}
