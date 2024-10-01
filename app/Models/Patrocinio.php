<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Patrocinio extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'id_beneficiario',
        'id_subprograma',
        'data_inicio',
        'data_fim',
        'status',
        'observacoes',
        'id_criador',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['id_beneficiario', 'id_subprograma', 'data_fim', 'status', 'observatories', 'id_criador']);
        // Chain fluent methods for configuration options
    }

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
