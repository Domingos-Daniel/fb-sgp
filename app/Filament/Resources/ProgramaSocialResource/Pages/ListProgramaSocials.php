<?php

namespace App\Filament\Resources\ProgramaSocialResource\Pages;

use App\Filament\Resources\ProgramaSocialResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProgramaSocials extends ListRecords
{
    protected static string $resource = ProgramaSocialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
