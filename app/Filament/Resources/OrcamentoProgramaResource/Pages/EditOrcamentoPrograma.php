<?php

namespace App\Filament\Resources\OrcamentoProgramaResource\Pages;

use App\Filament\Resources\OrcamentoProgramaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrcamentoPrograma extends EditRecord
{
    protected static string $resource = OrcamentoProgramaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
