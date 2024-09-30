<?php

namespace App\Filament\Resources\OrcamentoProgramaResource\Pages;

use App\Filament\Resources\OrcamentoProgramaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOrcamentoPrograma extends ViewRecord
{
    protected static string $resource = OrcamentoProgramaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
