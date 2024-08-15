<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Actions\Action as B;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        $id = $this->record->id;
        return Notification::make()
            ->success()
            ->title('Utilizador Actualizado')
            ->body('O utilizador foi editado com sucesso.')
            ->actions([
                B::make('view')
                    ->label('Visualizar')
                    ->button()
                    ->url(route('filament.admin.resources.users.view', ['record' => $id]))
            ])
            ->sendToDatabase(\auth()->user());
    }

}
