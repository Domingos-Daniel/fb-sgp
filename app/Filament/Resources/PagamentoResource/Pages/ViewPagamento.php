<?php

namespace App\Filament\Resources\PagamentoResource\Pages;

use App\Filament\Resources\PagamentoResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPagamento extends ViewRecord
{
    protected static string $resource = PagamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
