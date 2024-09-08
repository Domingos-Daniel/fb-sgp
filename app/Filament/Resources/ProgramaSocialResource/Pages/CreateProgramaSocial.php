<?php

namespace App\Filament\Resources\ProgramaSocialResource\Pages;

use App\Filament\Resources\ProgramaSocialResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProgramaSocial extends CreateRecord
{
    protected static string $resource = ProgramaSocialResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id_criador'] = auth()->id();

        return $data;
    }

}
