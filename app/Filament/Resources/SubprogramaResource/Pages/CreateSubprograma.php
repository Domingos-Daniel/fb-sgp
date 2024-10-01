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
        $subprograma = $this->record;

        // Insert a record into 'gastos' table
        Gasto::create([
            'id_programa' => $subprograma->id_programa,
            'id_subprograma' => $subprograma->id,
            'valor_gasto' => $subprograma->valor,
            'id_criador' => auth()->id(),
        ]);

        // Optionally, you can send a notification
        Notification::make()
            ->success()
            ->title('Subprograma criado')   
            ->body('Subprograma criado com sucesso.')
            ->sendToDatabase(auth()->user());
        //$this->notify('success', 'Subprograma criado e gasto registrado com sucesso.');
    }

}
