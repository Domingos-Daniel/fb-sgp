<?php

namespace App\Notifications;

use App\Models\OrcamentoGeral;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class OrcamentoReprovadoNotification extends Notification
{
    protected $orcamentoGeral;

    public function __construct(OrcamentoGeral $orcamentoGeral)
    {
        $this->orcamentoGeral = $orcamentoGeral;
    }

    public function via($notifiable)
    {
        return ['database']; // Apenas via banco de dados
    }

    public function toDatabase($notifiable)
    {
        return [
            'id_orcamento' => $this->orcamentoGeral->id,
            'mensagem' => 'Seu orçamento foi reprovado. Motivo: ' . $this->orcamentoGeral->workflow->observacoes,
        ];
    }
}
