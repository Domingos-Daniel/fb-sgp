<?php

namespace App\Filament\Resources\OrcamentoGeralResource\Pages;

use App\Filament\Resources\OrcamentoGeralResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOrcamentoGeral extends EditRecord
{
    protected static string $resource = OrcamentoGeralResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
