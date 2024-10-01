<?php

namespace App\Filament\Resources\PatrocinioResource\Pages;

use App\Filament\Resources\PatrocinioResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPatrocinio extends EditRecord
{
    protected static string $resource = PatrocinioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
