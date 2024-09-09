<?php

namespace App\Filament\Resources\SubprogramaResource\Pages;

use App\Filament\Resources\SubprogramaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSubprograma extends ViewRecord
{
    protected static string $resource = SubprogramaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
