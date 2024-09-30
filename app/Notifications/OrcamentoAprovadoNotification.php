<?php

namespace App\Notifications;

use App\Models\OrcamentoGeral;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class OrcamentoAprovadoNotification extends Notification
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
            'orcamento_geral_id' => $this->orcamentoGeral->id,
            'mensagem' => 'Seu orçamento foi aprovado.',
        ];
    }
}
