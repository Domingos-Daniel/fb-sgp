<?php

namespace App\Filament\Exports;

use App\Models\Beneficiario;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class BeneficiarioExporter extends Exporter
{
    protected static ?string $model = Beneficiario::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('tipo_beneficiario'),
            ExportColumn::make('nome'),
            ExportColumn::make('imagem'),
            ExportColumn::make('bi'),
            ExportColumn::make('nif'),
            ExportColumn::make('data_nascimento'),
            ExportColumn::make('genero'),
            ExportColumn::make('email'),
            ExportColumn::make('telemovel'),
            ExportColumn::make('telemovel_alternativo'),
            ExportColumn::make('endereco'),
            ExportColumn::make('pais'),
            ExportColumn::make('provincia'),
            ExportColumn::make('coordenadas_bancarias'),
            ExportColumn::make('ano_frequencia'),
            ExportColumn::make('curso'),
            ExportColumn::make('universidade_ou_escola'),
            ExportColumn::make('observacoes'),
            ExportColumn::make('user.name')
                ->label('Criado por'),
            ExportColumn::make('created_at')
                ->label('Criado em'),
            ExportColumn::make('updated_at')
                ->label('Atualizado em'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'A sua exportação de beneficiários foi concluída e ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exportadas.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' falharam a exportação.';
        }

        return $body;
    }
}
