<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatrocinioResource\RelationManagers\PagamentosRelationManager;
use App\Filament\Resources\PatrocinioResource\Pages;
use App\Filament\Resources\PatrocinioResource\RelationManagers;
use App\Models\Beneficiario;
use App\Models\Patrocinio;
use App\Models\Subprograma;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section as ComponentsSection;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatrocinioResource extends Resource
{
    protected static ?string $model = Patrocinio::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Patrocínio';
    protected static ?string $pluralModelLabel = 'Patrocínios';

    protected static ?string $navigationGroup = 'Gestão de Beneficiários';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('id_beneficiario')
                    ->label('Beneficiário')
                    ->options(Beneficiario::whereDoesntHave('patrocinios', function ($query) {
                        $query->where('status', 'ativo');
                    })->pluck('nome', 'id')->toArray())
                    ->required()
                    ->searchable(),

                    Forms\Components\Select::make('id_subprograma')
                    ->label('Subprograma')
                    ->options(Subprograma::pluck('descricao', 'id')->toArray())
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state) {
                            // Buscar o subprograma selecionado e sua duração
                            $subprograma = Subprograma::find($state);
                            if ($subprograma) {
                                $set('data_inicio', now()->format('Y-m-d')); // Define a data de início como o dia atual
                                
                                // Se a duração estiver definida no subprograma, calcula a data de fim
                                $duracao = $subprograma->duracao_patrocinio;
                                if ($duracao) {
                                    $dataFim = Carbon::parse($get('data_inicio'))->addMonths($duracao)->format('Y-m-d');
                                    $set('data_fim', $dataFim);
                                } else {
                                    $set('data_fim', null); // Se a duração não estiver definida, limpa a data de fim
                                }
                            }
                        }
                    }),
                
                    Forms\Components\DatePicker::make('data_inicio')
                    ->label('Data de Início')
                    ->required()
                    ->default(now()->format('Y-m-d'))
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        // Quando a data de início mudar, recalcula a data de fim com base no subprograma selecionado
                        $subprogramaId = $get('id_subprograma');
                        if ($subprogramaId) {
                            $subprograma = Subprograma::find($subprogramaId);
                            if ($subprograma) {
                                $duracao = $subprograma->duracao_patrocinio;
                                if ($duracao) {
                                    $dataFim = Carbon::parse($state)->addMonths($duracao)->format('Y-m-d');
                                    $set('data_fim', $dataFim);
                                }
                            }
                        }
                    }),
                
                    Forms\Components\DatePicker::make('data_fim')
                    ->label('Data de Fim')
                    ->required()
                    ->disabled() // Desativa o campo para impedir edição manual
                    ->default(now()->addMonth()->format('Y-m-d')) // Define um valor padrão inicial
                    ->reactive(), // Permite reatividade para ser atualizado automaticamente

                Forms\Components\Textarea::make('observacoes')
                    ->label('Observações')
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('id_criador')
                    ->default(auth()->id()),
            ]);
    }

    protected function getSubprogramaDuracao($id_subprograma)
    {
        $subprograma = \App\Models\Subprograma::find($id_subprograma);
        return $subprograma ? $subprograma->duracao_patrocinio : 0;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('beneficiario.nome')
                    ->label('Beneficiário')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('subprograma.descricao')
                    ->label('Subprograma')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->icon(
                        fn ($state) => match ($state) {
                            'ativo' => 'heroicon-o-check-circle',
                            'expirado' => 'heroicon-o-x-circle',
                            default => 'heroicon-o-clock',
                        }
                    )
                    ->colors([
                        'pendente' => 'warning',
                        'aprovado' => 'success',
                        'reprovado' => 'danger',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_inicio')
                    ->label('Data de Início')
                    ->date()
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_fim')
                    ->label('Data de Fim')
                    ->date()
                    ->badge()
                    ->color(
                        fn ($state) => $state === 'expirado' ? 'danger' : 'info'
                    )
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
                ComponentsSection::make('Detalhes do Patrocínio')
                    ->schema([
                        TextEntry::make('beneficiario.nome')
                            ->label('Beneficiário')
                            ->badge(),

                        TextEntry::make('subprograma.descricao')
                            ->label('Subprograma')
                            ->badge(),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->icon(
                                fn ($state) => match ($state) {
                                    'ativo' => 'heroicon-o-check-circle',
                                    'expirado' => 'heroicon-o-x-circle',
                                    default => 'heroicon-o-clock',
                                }
                            )
                            ->colors([
                                'pendente' => 'warning',
                                'aprovado' => 'success',
                                'reprovado' => 'danger',
                            ]),

                        TextEntry::make('data_inicio')
                            ->label('Data de Início')
                            ->date(),

                        TextEntry::make('data_fim')
                            ->label('Data de Fim')
                            ->date(),

                        TextEntry::make('observacoes')
                            ->label('Observações')
                            ->html(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
            PagamentosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPatrocinios::route('/'),
            'create' => Pages\CreatePatrocinio::route('/create'),
            'view' => Pages\ViewPatrocinio::route('/{record}'),
            'edit' => Pages\EditPatrocinio::route('/{record}/edit'),
        ];
    }
}
