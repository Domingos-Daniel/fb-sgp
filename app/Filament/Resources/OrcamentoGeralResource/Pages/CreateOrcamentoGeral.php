<?php

namespace App\Filament\Resources\OrcamentoGeralResource\Pages;

use App\Filament\Resources\OrcamentoGeralResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateOrcamentoGeral extends CreateRecord
{
    protected static string $resource = OrcamentoGeralResource::class;

    protected function afterCreate(): void
    {
        try {
            $this->record->iniciarFluxoAprovacao();
        } catch (\Exception $e) {
            \Log::error('Erro ao iniciar o fluxo de aprovação: ' . $e->getMessage());
            Notification::make()
                ->title('Erro')
                ->body('Não foi possível iniciar o fluxo de aprovação.')
                ->danger()
                ->send();
        }
    }
}
