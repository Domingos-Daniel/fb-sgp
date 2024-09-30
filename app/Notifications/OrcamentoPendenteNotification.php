<?php

namespace App\Notifications;

use App\Models\OrcamentoGeral;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OrcamentoPendenteNotification extends Notification
{
    use Queueable;

    protected $orcamentoGeral;

    public function __construct(OrcamentoGeral $orcamentoGeral)
    {
        $this->orcamentoGeral = $orcamentoGeral;
    }

    public function via($notifiable)
    {
        return ['database', 'mail']; // Envia por email e armazena no banco
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Orçamento Geral Pendente de Aprovação')
                    ->line('Um novo orçamento geral está pendente de sua aprovação.')
                    ->action('Ver Orçamento', url('/orcamentos-gerais/' . $this->orcamentoGeral->id))
                    ->line('Obrigado!');
    }

    public function toArray($notifiable)
    {
        return [
            'orcamento_geral_id' => $this->orcamentoGeral->id,
            'mensagem' => 'Um novo orçamento geral está pendente de sua aprovação.',
        ];
    }
}
