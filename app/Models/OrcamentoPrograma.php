<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrcamentoPrograma extends Model
{
    use HasFactory;

    protected $fillable = ['programa_social_id', 'orcamento_geral_id', 'valor', 'status', 'id_criador'];

    public function programaSocial()
    {
        return $this->belongsTo(ProgramaSocial::class);
    }

    public function orcamentoGeral()
    {
        return $this->belongsTo(OrcamentoGeral::class);
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'id_criador');
    }

    // Método para iniciar o fluxo de aprovação
    public function iniciarFluxoAprovacao()
    {
        // Lógica para iniciar o workflow
    }
}
