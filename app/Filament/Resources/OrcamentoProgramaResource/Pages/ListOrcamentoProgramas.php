<?php

namespace App\Filament\Resources\OrcamentoProgramaResource\Pages;

use App\Filament\Resources\OrcamentoProgramaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrcamentoProgramas extends ListRecords
{
    protected static string $resource = OrcamentoProgramaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
