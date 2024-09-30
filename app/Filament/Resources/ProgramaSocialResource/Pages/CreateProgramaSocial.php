<?php

namespace App\Filament\Resources\ProgramaSocialResource\Pages;

use App\Filament\Resources\ProgramaSocialResource;
use App\Models\OrcamentoPrograma;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateProgramaSocial extends CreateRecord
{
    protected static string $resource = ProgramaSocialResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id_criador'] = auth()->id();

        return $data;
    }
    // Método executado após a criação do registro
    protected function afterCreate(): void
    {
        $record = $this->record; // Obtém o registro do programa social criado
        $data = $this->form->getState(); // Obtém o estado do formulário (os valores preenchidos)

        // Cria o registro em OrcamentoPrograma
        OrcamentoPrograma::create([
            'id_programa' => $record->id,
            'id_orcamento' => $data['id_orcamento'],
            'valor' => $data['valor'],
            'id_criador' => auth()->user()->id,
        ]);

        // Notificação de sucesso
        Notification::make()
            ->success()
            ->title('Programa Social criado com sucesso!')
            ->body("Programa Social {$record->titulo} e seu orçamento criados com sucesso!")
            ->sendToDatabase(\auth()->user());
    }
}
