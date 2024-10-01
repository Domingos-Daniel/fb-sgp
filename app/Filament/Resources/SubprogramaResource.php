<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubprogramaResource\Pages;
use App\Filament\Resources\SubprogramaResource\RelationManagers\PatrociniosRelationManager;
use App\Models\Gasto;
use App\Models\ProgramaSocial;
use App\Models\Subprograma;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Filament\Infolists\Components;
use Filament\Infolists\Infolist;
use Closure;

class SubprogramaResource extends Resource
{
    protected static ?string $model = Subprograma::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Subprogramas';
    protected static ?string $pluralModelLabel = 'Subprogramas';
    protected static ?string $navigationGroup = 'Gestão de Programas Sociais';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Campo Select para selecionar o Programa Social
                Forms\Components\Select::make('id_programa')
                    ->label('Programa Social')
                    ->options(
                        ProgramaSocial::whereHas('orcamentoPrograma') // Filtra apenas programas com orçamento
                            ->pluck('titulo', 'id')
                            ->toArray()
                    )
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $orcamentoDisponivel = self::calcularOrcamentoDisponivel($state);
                            $set('orcamento_disponivel', $orcamentoDisponivel);
                        } else {
                            $set('orcamento_disponivel', 0);
                        }
                    }),

                // Exibir orçamento disponível para o programa selecionado
                Forms\Components\TextInput::make('orcamento_disponivel')
                    ->label('Orçamento Disponível (USD)')
                    ->disabled()
                    ->numeric()
                    ->default(0)
                    ->reactive()
                    ->dehydrated(false),

                // Campo de texto para a descrição do subprograma
                Forms\Components\TextInput::make('descricao')
                    ->label('Designação')
                    ->required()
                    ->maxLength(255),

                // Campo de valor para o subprograma
                Forms\Components\TextInput::make('valor')
                    ->label('Valor do Subprograma (USD)')
                    ->numeric()
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, $get) {
                        $orcamentoDisponivel = $get('orcamento_disponivel');
                        if ($state > $orcamentoDisponivel) {
                            Notification::make()
                                ->title('Erro')
                                ->body('O valor do subprograma não pode exceder o orçamento disponível.')
                                ->danger()
                                ->send();
                            $set('valor', null);
                        }
                    }),

                // Campo Select para o tipo de pagamento (anual, trimestral, etc.)
                Forms\Components\Select::make('tipo_pagamento')
                    ->label('Tipo de Pagamento')
                    ->options([
                        'trimestral' => 'Trimestral',
                        'semestral' => 'Semestral',
                        'anual' => 'Anual',
                    ])
                    ->required(),

                // Campo de duração do patrocínio (em meses)
                Forms\Components\TextInput::make('duracao_patrocinio')
                    ->label('Duração do Patrocínio (meses)')
                    ->numeric()
                    ->required(),

                // Campo oculto para o ID do criador
                Forms\Components\Hidden::make('id_criador')
                    ->default(auth()->id()),
            ]);
    }

    // Função para calcular o orçamento disponível para o programa selecionado
    public static function calcularOrcamentoDisponivel($id_programa)
    {
        // Obtém o orçamento total do programa a partir do modelo OrcamentoPrograma
        $programa = ProgramaSocial::find($id_programa);
        $orcamentoTotal = $programa->orcamentoPrograma->valor ?? 0;

        // Soma todas as despesas (gastos) do programa no ano atual
        $gastosTotais = Gasto::where('id_programa', $id_programa)
            ->whereYear('created_at', now()->year)
            ->sum('valor_gasto');

        // Calcula o orçamento restante
        $orcamentoDisponivel = $orcamentoTotal - $gastosTotais;

        return $orcamentoDisponivel;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('programaSocial.titulo')
                    ->label('Programa Social')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('descricao')
                    ->label('Designação')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('valor')
                    ->label('Valor do Subprograma (USD)')
                    ->money('usd', true)
                    ->sortable(),

                    Tables\Columns\TextColumn::make('tipo_pagamento')
                    ->label('Tipo de Pagamento')
                    ->sortable()
                    ->badge()
                    ->colors([
                        'trimestral' => 'success',
                        'semestral' => 'warning',
                        'anual' => 'info',
                    ]),

                Tables\Columns\TextColumn::make('duracao_patrocinio')
                    ->label('Duração do Patrocínio (meses)')
                    ->sortable(),

                Tables\Columns\TextColumn::make('valor_restante')
                    ->label('Valor Restante do Programa (USD)')
                    ->getStateUsing(function ($record) {
                        return self::calcularOrcamentoDisponivel($record->id_programa); // Chama o método estático para calcular o valor restante
                    })
                    ->money('usd', true)
                    ->color(fn ($state) => $state < 1000 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Section::make('Detalhes do Subprograma')
                    ->schema([
                        Components\TextEntry::make('descricao')
                            ->label('Designação')
                            ->badge(),

                        Components\TextEntry::make('programaSocial.titulo')
                            ->label('Programa Social')
                            ->badge(),

                        Components\TextEntry::make('valor')
                            ->label('Valor do Subprograma (USD)')
                            ->money('usd', true)
                            ->badge(),

                            Components\TextEntry::make('tipo_pagamento')
                            ->label('Tipo de Pagamento')
                            ->badge()
                            ->color(fn ($record) => match ($record->tipo_pagamento) {
                                'trimestral' => 'success',
                                'semestral' => 'warning',
                                'anual' => 'info',
                                default => 'secondary',
                            }),

                        Components\TextEntry::make('duracao_patrocinio')
                            ->label('Duração do Patrocínio (meses)')
                            ->badge(),

                        Components\TextEntry::make('valor_restante')
                            ->label('Valor Restante do Programa (USD)')
                            ->getStateUsing(function ($record) {
                                return self::calcularOrcamentoDisponivel($record->id_programa); // Chama o método estático para calcular o valor restante
                            })
                            ->money('usd', true)
                            ->color(fn ($state) => $state < 1000 ? 'danger' : 'success'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
            PatrociniosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubprogramas::route('/'),
            'create' => Pages\CreateSubprograma::route('/create'),
            'view' => Pages\ViewSubprograma::route('/{record}'),
            'edit' => Pages\EditSubprograma::route('/{record}/edit'),
        ];
    }
}
