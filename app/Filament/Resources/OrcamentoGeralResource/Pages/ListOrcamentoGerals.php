<?php

namespace App\Filament\Resources\OrcamentoGeralResource\Pages;

use App\Filament\Resources\OrcamentoGeralResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOrcamentoGerals extends ListRecords
{
    protected static string $resource = OrcamentoGeralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
