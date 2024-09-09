<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    use HasFactory;
    protected $fillable = [
        'id_programa',
        'id_subprograma',
        'id_subprograma_pessoa',
        'valor_gasto',
        'created_at',
        'updated_at',
    ];

    public function programa()
    {
        return $this->belongsTo(ProgramaSocial::class, 'id_programa');
    }

    public function subprograma()
    {
        return $this->belongsTo(Subprograma::class, 'id_subprograma');
    }

}
