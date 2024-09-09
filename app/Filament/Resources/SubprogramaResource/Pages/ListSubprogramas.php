<?php

namespace App\Filament\Resources\SubprogramaResource\Pages;

use App\Filament\Resources\SubprogramaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubprogramas extends ListRecords
{
    protected static string $resource = SubprogramaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
