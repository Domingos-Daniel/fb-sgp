<?php

namespace App\Filament\Resources\PatrocinioResource\Pages;

use App\Filament\Resources\PatrocinioResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPatrocinio extends ViewRecord
{
    protected static string $resource = PatrocinioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
