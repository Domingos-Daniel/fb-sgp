<?php

namespace App\Filament\Resources\OrcamentoGeralResource\Pages;

use App\Filament\Resources\OrcamentoGeralResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\RichEditor;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Pages\Actions\Action;

class ViewOrcamentoGeral extends ViewRecord
{
    protected static string $resource = OrcamentoGeralResource::class;

    protected function getHeaderActions(): array
    {

        $user = auth()->user();
        $record = $this->record;

        // Determina a etapa atual do workflow e o status do orçamento
        $etapaAtual = $record->workflow->etapa ?? 0;
        $status = $record->status ?? 'Pendente';

        // Define se o usuário atual pode aprovar ou reprovar com base na role e na etapa
        $canApprove = $this->canUserApprove($user, $record, $etapaAtual);

        // Cria as ações    
        return [
            Actions\EditAction::make(),
            Actions\CreateAction::make()
                ->color('info'),
            Action::make('aprovar')
                ->label('Aprovar')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $canApprove && $status === 'Pendente')
                ->requiresConfirmation()
                ->modalHeading('Aprovar Orçamento')
                ->modalSubheading('Deseja realmente aprovar este orçamento?')
                ->action(function () use ($record, $etapaAtual, $user) {
                    $record->status = $etapaAtual == 1 && $record->valor_total < 500000 ? 'Aprovado' : 'Pendente';
                    $record->save();

                    // Atualiza o workflow
                    $record->workflow->status = 'Aprovado';
                    $record->workflow->aprovador_id = $user->id;
                    $record->workflow->data_aprovacao = now();
                    $record->workflow->save();

                    Notification::make()
                        ->title('Orçamento Aprovado')
                        ->body("O orçamento #{$record->id} foi aprovado com sucesso.")
                        ->success()
                        ->persistent()
                        ->send();

                    // Verifica se é necessário avançar para a próxima etapa
                    if ($record->valor_total >= 500000 && $etapaAtual == 1) {
                        $record->workflow->status = 'Pendente';
                        $record->workflow->etapa = 2; // Avança para a próxima etapa
                        $record->workflow->aprovador_id = null;
                        $record->workflow->data_aprovacao = null;
                        $record->workflow->save();

                        // Notifica os membros do CA Curadores
                        $caCuradores = User::role('CA Curadores')->get();
                        Notification::make()
                            ->title('Orçamento Pendente de Aprovação')
                            ->body("O orçamento #{$record->id} foi aprovado pelo DG e está pendente de sua aprovação.")
                            ->persistent()
                            ->sendToDatabase($caCuradores)
                            ->send();
                    }

                    $this->redirect($this->getResource()::getUrl('view', ['record' => $record->id]));
                }),

            Action::make('reprovar')
                ->label('Reprovar')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $canApprove && $status === 'Pendente')
                ->requiresConfirmation()
                ->modalHeading('Reprovar Orçamento')
                ->modalSubheading('Deseja realmente reprovar este orçamento? Por favor, forneça o motivo.')
                ->form([
                    RichEditor::make('motivo_reprovacao')
                        ->label('Motivo da Reprovação')
                        ->required()
                        ->placeholder('Descreva o motivo da reprovação'),
                ])
                ->action(function (array $data) use ($record, $user) {
                    $record->status = 'Rejeitado';
                    $record->observacoes = $data['motivo_reprovacao'];
                    $record->save();

                    // Atualiza o workflow
                    $record->workflow->status = 'Rejeitado';
                    $record->workflow->aprovador_id = $user->id;
                    $record->workflow->data_aprovacao = now();
                    $record->workflow->observacoes = $data['motivo_reprovacao'];
                    $record->workflow->save();

                    Notification::make()
                        ->title('Orçamento Rejeitado')
                        ->body("O orçamento #{$record->id} foi rejeitado. Motivo: {$data['motivo_reprovacao']}")
                        ->danger()
                        ->persistent()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('view', ['record' => $record->id]));
                }),
        ];
    }

    /**
     * Método que define se o usuário atual pode aprovar ou reprovar um orçamento com base em suas permissões e etapa do workflow.
     *
     * @param User $user
     * @param $record
     * @param int $etapaAtual
     * @return bool
     */
    protected function canUserApprove(User $user, $record, int $etapaAtual): bool
    {
        if ($record->status !== 'Pendente') {
            return false;
        }

        // Verifica se o usuário é um DG e o orçamento é menor que 500,000 USD ou está na etapa 1
        if ($user->hasRole('DG') && ($record->valor_total < 500000 || $etapaAtual == 1)) {
            return true;
        }

        // Verifica se o usuário é um CA Curadores e o orçamento está na etapa 2
        if ($user->hasRole('CA Curadores') && $record->valor_total >= 500000 && $etapaAtual == 2) {
            return true;
        }

        return false;
    }
}
