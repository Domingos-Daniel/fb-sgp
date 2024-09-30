<?php

namespace App\Filament\Resources\ProgramaSocialResource\Pages;

use App\Filament\Resources\ProgramaSocialResource;
use Filament\Actions;
use Filament\Actions\Modal\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewProgramaSocial extends ViewRecord
{
    protected static string $resource = ProgramaSocialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make()
            ->requiresConfirmation(),
            Actions\CreateAction::make()
            ->color('info'),
        ];
    }
}
