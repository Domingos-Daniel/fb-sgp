<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrcamentoPrograma extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_programa', 
        'id_orcamento', 
        'valor', 
        'status', 
        'id_criador'];

    public function programaSocial()
    {
        return $this->belongsTo(ProgramaSocial::class);
    }

    // Relação com Programa
    public function programa(): BelongsTo
    {
        return $this->belongsTo(ProgramaSocial::class, 'id_programa');
    }

    // Relação com Orcamento
    public function orcamento(): BelongsTo
    {
        return $this->belongsTo(OrcamentoGeral::class, 'id_orcamento');
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
