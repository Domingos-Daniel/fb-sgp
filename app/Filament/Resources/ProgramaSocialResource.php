<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramaSocialResource\Pages;
use App\Filament\Resources\ProgramaSocialResource\RelationManagers;
use App\Models\ProgramaSocial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProgramaSocialResource extends Resource
{
    protected static ?string $model = ProgramaSocial::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'Programa Social';
    protected static ?string $pluralModelLabel  = 'Programas Sociais';
    protected static ?string $navigationGroup = 'Gestão de Programas Sociais';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('titulo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('descricao')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('publico_alvo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('meta')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('orcamento')
                    ->label("Valor do Orçamento")
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->numeric()
                    ->prefixIcon('heroicon-o-currency-dollar')
                    ->prefixIconColor('success'),
                Forms\Components\Hidden::make('id_criador')
                    ->default(auth()->user()->id),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titulo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('publico_alvo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('id_criador')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
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
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListProgramaSocials::route('/'),
            'create' => Pages\CreateProgramaSocial::route('/create'),
            'view' => Pages\ViewProgramaSocial::route('/{record}'),
            'edit' => Pages\EditProgramaSocial::route('/{record}/edit'),
        ];
    }
}
