<?php

namespace App\Filament\Resources\ProgramaSocialResource\Pages;

use App\Filament\Resources\ProgramaSocialResource;
use App\Models\OrcamentoPrograma;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProgramaSocial extends EditRecord
{
    protected static string $resource = ProgramaSocialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['id_criador'] = auth()->id();

        return $data;
    }

    protected function afterSave(): void
{
    $record = $this->record; // Obtém o registro do programa social atualizado
    $data = $this->form->getState(); // Obtém o estado do formulário (os valores preenchidos)

    // Buscar o registro de OrcamentoPrograma relacionado ao programa
    $orcamentoPrograma = OrcamentoPrograma::where('id_programa', $record->id)->first();

    // Verifica se o registro de OrcamentoPrograma existe
    if ($orcamentoPrograma) {
        // Atualiza o registro existente
        $orcamentoPrograma->update([
            'id_orcamento' => $data['id_orcamento'], // Nome correto do campo no formulário
            'valor' => $data['valor'],   // Nome correto do campo no formulário
        ]);

        // Notificação de sucesso
        Notification::make()
            ->success()
            ->title('Programa Social atualizado com sucesso!')
            ->body("Programa Social {$record->titulo} e seu orçamento atualizados com sucesso!")
            ->sendToDatabase(auth()->user());
    } else {
        // Se não existir, cria um novo registro em OrcamentoPrograma
        OrcamentoPrograma::create([
            'id_programa' => $record->id,
            'id_orcamento' => $data['id_orcamento'],
            'valor' => $data['valor'],
            'id_criador' => auth()->user()->id,
        ]);

        // Notificação de sucesso ao criar
        Notification::make()
            ->success()
            ->title('Programa Social atualizado com sucesso!')
            ->body("Programa Social {$record->titulo} e seu orçamento foram criados com sucesso!")
            ->sendToDatabase(auth()->user());
    }
}

}
