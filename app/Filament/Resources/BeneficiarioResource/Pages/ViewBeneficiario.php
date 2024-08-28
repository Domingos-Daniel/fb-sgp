<?php

namespace App\Filament\Resources\BeneficiarioResource\Pages;

use App\Filament\Resources\BeneficiarioResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewBeneficiario extends ViewRecord
{
    protected static string $resource = BeneficiarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar Beneficiário')
                ->icon('heroicon-o-pencil')
                ->color('info'),
            Actions\CreateAction::make()
                ->label('Adicionar Beneficiário')
                ->icon('heroicon-o-user-plus')
                ->color('success'),
            Actions\Action::make('exportPdf')
                ->label('Exportar PDF')
                ->color('danger')
                ->icon('heroicon-o-document-text')
                ->action(function ($record) {
                    return redirect()->route('beneficiarios.export-pdf', $record->id);
                }),
        ];
    }
}
