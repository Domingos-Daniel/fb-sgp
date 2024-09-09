<?php

namespace App\Filament\Resources\SubprogramaResource\Pages;

use App\Filament\Resources\SubprogramaResource;
use App\Models\Gasto;
use App\Models\Subprograma;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateSubprograma extends CreateRecord
{
    protected static string $resource = SubprogramaResource::class;

    protected function mutateDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->user()->id;

        return $data;
    }

    protected function afterCreate(): void
{
    try {
        // Verifica se o Subprograma existe
        $subprograma = Subprograma::find($this->record['id']);

        if (!$subprograma) {
            throw new \Exception('Subprograma não encontrado.');
        }

        // Verifica se o Programa Social associado ao Subprograma existe
        $programa = $subprograma->programaSocial; // Acessando a relação correta

        if (!$programa) {
            throw new \Exception('Programa Social associado ao subprograma não encontrado.');
        }

        

        // Cria o registro de Gasto
        $gasto = new Gasto();
        $gasto->id_programa = $programa->id;
        $gasto->id_subprograma = $subprograma->id;
        $gasto->valor_gasto = $this->record['valor'];

        // Salvando o gasto
        $gasto->save();

        

        // Notificação de sucesso
        Notification::make()
            ->title('Subprograma Adicionado com Sucesso')
            ->body('O seu subprograma foi adicionado com sucesso.')
            ->success()
            ->sendToDatabase(\auth()->user())
            ->send();
    } catch (\Exception $e) {
        // Notificação de erro
        Notification::make()
            ->title('Erro ao Salvar')
            ->body('Erro na inserção dos dados. Por favor, tente novamente: ' . $e->getMessage())
            ->danger()
            ->sendToDatabase(\auth()->user())
            ->send();
    }
}


}
