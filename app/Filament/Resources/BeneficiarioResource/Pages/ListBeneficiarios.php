<?php

namespace App\Filament\Resources\BeneficiarioResource\Pages;

use App\Filament\Resources\BeneficiarioResource;
use App\Filament\Resources\BeneficiarioResource\Widgets\BeneficiarioStatsOverview;
use App\Imports\BeneficiariosImport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Str;
use YOS\FilamentExcel\Actions\Import;

class ListBeneficiarios extends ListRecords
{
    protected static string $resource = BeneficiarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Import::make()
            ->import(BeneficiariosImport::class)
            ->type(\Maatwebsite\Excel\Excel::XLSX)
            ->label('Importortar')
            ->hint('Carregue um ficheiro .xlsx')
            ->icon('heroicon-o-arrow-up-on-square')
            ->color('success'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            BeneficiarioStatsOverview::class,
        ];
    }

    public function updated($name)
    {
        if (Str::of($name)->contains(['mountedTableAction', 'mountedTableBulkAction'])) {
            $this->emit('updateBeneficiarioOverview');
        }
    }
}
