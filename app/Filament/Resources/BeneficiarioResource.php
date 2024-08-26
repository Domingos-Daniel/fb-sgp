<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BeneficiarioResource\Pages;
use App\Filament\Resources\BeneficiarioResource\RelationManagers;
use App\Models\Beneficiario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BeneficiarioResource extends Resource
{
    protected static ?string $model = Beneficiario::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('tipo_beneficiario')
                    ->required()
                    ->maxLength(255)
                    ->default('Individual'),
                Forms\Components\TextInput::make('nome')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('bi')
                    ->maxLength(255)
                    ->default('Não Aplicável'),
                Forms\Components\TextInput::make('nif')
                    ->maxLength(255)
                    ->default('Não Aplicável'),
                Forms\Components\DatePicker::make('data_nascimento'),
                Forms\Components\TextInput::make('genero')
                    ->maxLength(255)
                    ->default('Não Aplicável'),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                Forms\Components\TextInput::make('telemovel')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('telemovel_alternativo')
                    ->tel()
                    ->maxLength(255),
                Forms\Components\TextInput::make('endereco')
                    ->maxLength(255)
                    ->default('Não Aplicável'),
                Forms\Components\TextInput::make('pais')
                    ->maxLength(255)
                    ->default('Não Aplicável'),
                Forms\Components\TextInput::make('coordenadas_bancarias')
                    ->maxLength(255)
                    ->default('Não Aplicável'),
                Forms\Components\TextInput::make('ano_frequencia')
                    ->maxLength(255)
                    ->default('Não Aplicável'),
                Forms\Components\TextInput::make('curso')
                    ->maxLength(255)
                    ->default('Não Aplicável'),
                Forms\Components\TextInput::make('universidade_ou_escola')
                    ->maxLength(255)
                    ->default('Não Aplicável'),
                Forms\Components\Textarea::make('observacoes')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('id_criador')
                    ->required()
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tipo_beneficiario')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nif')
                    ->searchable(),
                Tables\Columns\TextColumn::make('data_nascimento')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('genero')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telemovel')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telemovel_alternativo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('endereco')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pais')
                    ->searchable(),
                Tables\Columns\TextColumn::make('coordenadas_bancarias')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ano_frequencia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('curso')
                    ->searchable(),
                Tables\Columns\TextColumn::make('universidade_ou_escola')
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
            'index' => Pages\ListBeneficiarios::route('/'),
            'create' => Pages\CreateBeneficiario::route('/create'),
            'view' => Pages\ViewBeneficiario::route('/{record}'),
            'edit' => Pages\EditBeneficiario::route('/{record}/edit'),
        ];
    }
}
