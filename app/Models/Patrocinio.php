<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patrocinio extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_beneficiario',
        'id_subprograma',
        'data_inicio',
        'data_fim',
        'status',
        'observacoes',
        'id_criador',
    ];

    // app/Models/Patrocinio.php

    public function getNomeCompletoAttribute()
    {
        $beneficiario = Beneficiario::find($this->id_beneficiario);
        $subprograma = Subprograma::find($this->id_subprograma);
        return $beneficiario->nome . ' - ' . $subprograma->descricao . ' - ' . $this->status;
    }

    public function beneficiario()
    {
        return $this->belongsTo(Beneficiario::class, 'id_beneficiario');
    }

    public function subprograma()
    {
        return $this->belongsTo(Subprograma::class, 'id_subprograma');
    }

    public function pagamentos()
    {
        return $this->hasMany(Pagamento::class, 'id_patrocinio');
    }
}
