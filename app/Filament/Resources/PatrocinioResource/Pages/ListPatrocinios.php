<?php

namespace App\Filament\Resources\PatrocinioResource\Pages;

use App\Filament\Resources\PatrocinioResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPatrocinios extends ListRecords
{
    protected static string $resource = PatrocinioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
