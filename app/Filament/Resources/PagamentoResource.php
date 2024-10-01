<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PagamentoResource\Pages;
use App\Models\Pagamento;
use App\Models\Patrocinio;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class PagamentoResource extends Resource
{
    protected static ?string $model = Pagamento::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Pagamentos';
    protected static ?string $pluralModelLabel = 'Pagamentos';
    protected static ?string $navigationGroup = 'Gestão de Beneficiários';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id_patrocinio')
                    ->label('Patrocínio')
                    ->options(
                        Patrocinio::where('status', 'ativo')
                            ->with(['beneficiario' => function ($query) {
                                $query->select('nome', 'id');
                            }, 'subprograma' => function ($query) {
                                $query->select('descricao', 'id');
                            }])
                            ->get()
                            ->map(function ($patrocinio) {
                                return [
                                    'id' => $patrocinio->id,
                                    'nome_completo' => $patrocinio->getNomeCompletoAttribute(),
                                ];
                            })
                            ->pluck('nome_completo', 'id')
                            ->toArray()
                    )
                    ->required()
                    ->searchable()
                    ->label('Selecione o Patrocínio Aprovado')
                    ->reactive()
                    ->afterStateUpdated(function ($state, $set) {
                        $patrocinio = Patrocinio::find($state);
                        $set('valor', $patrocinio->subprograma->valor);
                    }),
            
                Forms\Components\DatePicker::make('data_pagamento')
                    ->label('Data do Pagamento')
                    ->required()
                    ->default(now()),
            
                Forms\Components\TextInput::make('valor')
                    ->label('Valor (USD)')
                    ->numeric()
                    ->required()
                    ->readonly()
                    ->default(0),
            
                Forms\Components\Textarea::make('motivo_rejeicao')
                    ->label('Motivo da Rejeição')
                    ->hidden()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('patrocinio.beneficiario.nome')
                    ->label('Beneficiário')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('patrocinio.subprograma.descricao')
                    ->label('Subprograma')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('data_pagamento')
                    ->label('Data do Pagamento')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('valor')
                    ->label('Valor (USD)')
                    ->money('usd', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'pendente' => 'warning',
                        'aprovado' => 'success',
                        'reprovado' => 'danger',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('motivo_rejeicao')
                    ->label('Motivo da Rejeição')
                    ->hidden(
                        function ($record) {
                            return $record !== null && $record->status === 'reprovado';
                        }
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime(),
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
                    ->visible(fn ($record) => $record->status === 'pendente'),

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
                    ->visible(fn ($record) => $record->status === 'pendente'),

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
                
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Detalhes do Pagamento')
                    ->schema([
                        TextEntry::make('patrocinio.beneficiario.nome')
                            ->label('Beneficiário')
                            ->badge(),

                        TextEntry::make('patrocinio.subprograma.descricao')
                            ->label('Subprograma')
                            ->badge(),

                        TextEntry::make('data_pagamento')
                            ->label('Data do Pagamento')
                            ->date(),

                        TextEntry::make('valor')
                            ->label('Valor (USD)')
                            ->money('usd', true)
                            ->badge(),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->colors([
                                'pendente' => 'warning',
                                'aprovado' => 'success',
                                'reprovado' => 'danger',
                            ]),

                        TextEntry::make('motivo_rejeicao')
                            ->label('Motivo da Rejeição')
                            ->visible(fn ($record) => $record->status === 'reprovado')
                            ->html(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPagamentos::route('/'),
            'create' => Pages\CreatePagamento::route('/create'),
            'view' => Pages\ViewPagamento::route('/{record}'),
            'edit' => Pages\EditPagamento::route('/{record}/edit'),
        ];
    }
}
