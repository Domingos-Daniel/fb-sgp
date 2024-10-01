<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PatrocinioResource\Pages;
use App\Filament\Resources\PatrocinioResource\RelationManagers;
use App\Models\Beneficiario;
use App\Models\Patrocinio;
use App\Models\Subprograma;
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
                    ->options(Beneficiario::pluck('nome', 'id')->toArray())
                    ->required()
                    ->searchable(),

                Forms\Components\Select::make('id_subprograma')
                    ->label('Subprograma')
                    ->options(Subprograma::pluck('descricao', 'id')->toArray())
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $subprograma = Subprograma::find($state);
                            $set('data_inicio', now()->format('Y-m-d'));
                            $set('data_fim', $subprograma->duracao_patrocinio ? now()->addMonths($subprograma->duracao_patrocinio)->format('Y-m-d') : null);
                        }
                    }),

                Forms\Components\DatePicker::make('data_inicio')
                    ->label('Data de Início')
                    ->required()
                    ->default(now()->format('Y-m-d')),

                Forms\Components\DatePicker::make('data_fim')
                    ->label('Data de Fim')
                    ->required()
                    ->reactive(),

                Forms\Components\Textarea::make('observacoes')
                    ->label('Observações')
                    ->maxLength(255)
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('id_criador')
                    ->default(auth()->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('beneficiario.nome')
                    ->label('Beneficiário')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('subprograma.descricao')
                    ->label('Subprograma')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('data_inicio')
                    ->label('Data de Início')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('data_fim')
                    ->label('Data de Fim')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime(),
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
