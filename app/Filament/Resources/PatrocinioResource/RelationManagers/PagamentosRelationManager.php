<?php

namespace App\Filament\Resources\PatrocinioResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Collection;

class PagamentosRelationManager extends RelationManager
{
    protected static string $relationship = 'pagamentos';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                DatePicker::make('data_pagamento')
                    ->label('Data do Pagamento')
                    ->required(),

                TextInput::make('valor')
                    ->label('Valor (USD)')
                    ->numeric()
                    ->required(),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        'pendente' => 'Pendente',
                        'aprovado' => 'Aprovado',
                        'reprovado' => 'Reprovado',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('motivo_rejeicao')
                    ->label('Motivo da Rejeição')
                    ->visible(fn ($get) => $get('status') === 'reprovado'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('data_pagamento')
            ->columns([
                Tables\Columns\TextColumn::make('patrocinio.beneficiario.nome')
                    ->label('Beneficiário')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('patrocinio.subprograma.descricao')
                    ->label('Subprograma')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('data_pagamento')
                    ->label('Data do Pagamento')
                    ->badge()
                    ->color('info')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('valor')
                    ->label('Valor (USD)')
                    ->money('usd', true)
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->icon(
                        fn ($state) => match ($state) {
                            'pendente' => 'heroicon-o-clock',
                            'aprovado' => 'heroicon-o-check-circle',
                            'reprovado' => 'heroicon-o-x-circle',
                            default => 'heroicon-o-clock',
                        }
                    )
                    ->color(
                        fn ($state) => match ($state) {
                            'pendente' => 'warning',
                            'aprovado' => 'success',
                            'reprovado' => 'danger',
                            default => 'info',
                        }
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('next_payment_date')
                    ->dateTime()
                    ->label('Próximo Pagamento')
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->getStateUsing(function ($record) {
                        $subprograma = $record->patrocinio->subprograma;
                        $paymentType = $subprograma->tipo_pagamento;
                        $createdAt = $record->created_at;

                        switch ($paymentType) {
                            case 'mensal':
                                $nextPaymentDate = $createdAt->addMonth();
                                break;
                            case 'trimestral':
                                $nextPaymentDate = $createdAt->addMonths(3);
                                break;
                            case 'semestral':
                                $nextPaymentDate = $createdAt->addMonths(6);
                                break;
                            case 'anual':
                                $nextPaymentDate = $createdAt->addYear();
                                break;
                            default:
                                $nextPaymentDate = null;
                        }

                        return $nextPaymentDate ? $nextPaymentDate->format('d/m/Y H:i') : '';
                    }),

                Tables\Columns\TextColumn::make('motivo_rejeicao')
                    ->label('Motivo da Rejeição')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->hidden(
                        function ($record) {
                            return $record !== null && $record->status === 'reprovado';
                        }
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('aprovar')
                    ->label('Aprovar Pagamento')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->action(function ($record) {
                        $record->status = 'aprovado';
                        $record->motivo_rejeicao = null;
                        $record->save();

                        Notification::make()
                            ->title('Pagamento Aprovado')
                            ->body("O pagamento para o beneficiário {$record->patrocinio->beneficiario->nome} foi aprovado.")
                            ->success()
                            ->sendToDatabase(auth()->user())
                            ->send();
                    })
                    ->visible(fn($record) => $record->status === 'pendente'),

                Tables\Actions\Action::make('reprovar')
                    ->label('Reprovar Pagamento')
                    ->color('danger')
                    ->icon('heroicon-o-x-mark')
                    ->action(function ($record, $data) {
                        $record->status = 'reprovado';
                        $record->motivo_rejeicao = $data['motivo_rejeicao'];
                        $record->save();

                        Notification::make()
                            ->title('Pagamento Reprovado')
                            ->body("O pagamento para o beneficiário {$record->patrocinio->beneficiario->nome} foi reprovado.")
                            ->danger()
                            ->sendToDatabase(auth()->user())
                            ->send();
                    })
                    ->form([
                        Forms\Components\RichEditor::make('motivo_rejeicao')
                            ->label('Motivo da Rejeição')
                            ->required()
                            ->maxLength(255)
                    ])
                    ->modalHeading('Reprovar Pagamento')
                    ->modalSubheading('Por favor, informe o motivo da reprovação.')
                    ->visible(fn($record) => $record->status === 'pendente'),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('aprovarPagamentos')
                    ->label('Aprovar Pagamentos Selecionados')
                    ->color('success')
                    ->action(function (Collection $records) {
                        foreach ($records as $record) {
                            $record->status = 'aprovado';
                            $record->motivo_rejeicao = null;
                            $record->save();

                            Notification::make()
                                ->title('Pagamentos Aprovados')
                                ->body("Pagamentos selecionados foram aprovados com sucesso.")
                                ->success()
                                ->sendToDatabase(auth()->user())
                                ->send();
                        }
                    })
                    ->visible(true),

                Tables\Actions\BulkAction::make('reprovarPagamentos')
                    ->label('Reprovar Pagamentos Selecionados')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('motivo_rejeicao')
                            ->label('Motivo da Rejeição')
                            ->required()
                            ->maxLength(255)
                    ])
                    ->action(function (Collection $records, $data) {
                        foreach ($records as $record) {
                            $record->status = 'reprovado';
                            $record->motivo_rejeicao = $data['motivo_rejeicao'];
                            $record->save();

                            Notification::make()
                                ->title('Pagamentos Reprovados')
                                ->body("Pagamentos selecionados foram reprovados.")
                                ->danger()
                                ->sendToDatabase(auth()->user())
                                ->send();
                        }
                    })
                    ->visible(true),
            ]);
    }
}
