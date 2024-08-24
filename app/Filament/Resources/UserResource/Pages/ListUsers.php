<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Imports\UserImporter;
use App\Filament\Resources\UserResource;
use App\Imports\UsersImport;
use Filament\Actions;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Collection;
use YOS\FilamentExcel\Actions\Import;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Import::make()
            ->import(UsersImport::class)
            ->type(\Maatwebsite\Excel\Excel::XLSX)
            ->label('Import from excel')
            ->hint('Upload xlsx type')
            //->icon('')
            ->color('success'),
        ];
    }
}
