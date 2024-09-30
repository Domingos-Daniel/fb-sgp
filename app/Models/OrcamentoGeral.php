<?php

namespace App\Models;

use App\Notifications\OrcamentoPendenteNotification;
use Filament\Notifications\Livewire\Notifications;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrcamentoGeral extends Model
{
    use HasFactory;
    protected $table = 'orcamento_gerais';
    protected $fillable = ['valor_total', 'ano_semestre', 'status', 'id_criador', 'observacoes'];

    public function orcamentosProgramas()
    {
        return $this->hasMany(OrcamentoPrograma::class);
    }

    public function criador()
    {
        return $this->belongsTo(User::class, 'id_criador');
    }

    // Relação com OrcamentoPrograma
    public function programas(): HasMany
    {
        return $this->hasMany(OrcamentoPrograma::class, 'id_orcamento');
    }


    public function workflow2()
    {
        return $this->hasOne(Workflow::class, 'orcamento_geral_id');
    }


    public function workflow()
    {
        return $this->morphOne(Workflow::class, 'workflowable');
    }


    // Método para iniciar o fluxo de aprovação
    // No modelo OrcamentoGeral
    // Método para iniciar o fluxo de aprovação
    public function iniciarFluxoAprovacao()
    {
        // Cria um novo registro de Workflow
        $workflow = new Workflow();
        $workflow->workflowable()->associate($this);
        $workflow->status = 'Pendente';
        $workflow->etapa = 1; // Inicia na primeira etapa
        $workflow->save();

        // Notifica o DG ou DG e CA Curadores, conforme o valor do orçamento
        if ($this->valor_total < 500000) {
            // Notificar o DG
            $dgUsers = User::role('DG')->get();
            Notification::make()
                ->title('Orçamento Pendente de Aprovação')
                ->body('Um novo orçamento está pendente de sua aprovação.')
                ->persistent()
                ->sendToDatabase($dgUsers) // Envia a notificação ao banco de dados dos usuários DG
                ->send();
        } else {
            // Notificar o DG e o CA Curadores
            $dgUsers = User::role('DG')->get();
            $caCuradores = User::role('CA Curadores')->get();
            $aprovadores = $dgUsers->merge($caCuradores);

            Notifications::make()
                ->title('Orçamento Pendente de Aprovação')
                ->body('Um novo orçamento está pendente de sua aprovação.')
                ->persistent()
                ->sendToDatabase($aprovadores) // Envia a notificação ao banco de dados dos aprovadores
                ->send();
        }
    }
}
