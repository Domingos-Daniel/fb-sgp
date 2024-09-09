<?php

namespace App\Filament\Resources\SubprogramaResource\Pages;

use App\Filament\Resources\SubprogramaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubprograma extends EditRecord
{
    protected static string $resource = SubprogramaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
