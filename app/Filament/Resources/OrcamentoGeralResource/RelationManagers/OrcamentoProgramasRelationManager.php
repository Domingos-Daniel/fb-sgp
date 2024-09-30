<?php

namespace App\Filament\Resources\OrcamentoGeralResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrcamentoProgramasRelationManager extends RelationManager
{
    protected static string $relationship = 'orcamentoProgramas';
    protected static ?string $recordTitleAttribute = 'programa.titulo';
    protected static bool $isLazy = false;

    public function form(Form $form): Form
    {
         return $form
            ->schema([
                Forms\Components\Select::make('id_programa')
                    ->label('Programa Social')
                    ->relationship('programa', 'titulo')
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('valor')
                    ->label('Valor Atribuído ao Programa (USD)')
                    ->numeric()
                    ->required(),

                Forms\Components\Textarea::make('observacoes')
                    ->label('Observações'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Titulo')
            ->columns([
                Tables\Columns\TextColumn::make('programa.titulo')
                    ->label('Programa Social')
                    ->badge()
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('valor')
                    ->label('Valor Atribuído (USD)')
                    ->color('info')
                    ->badge()
                    ->formatStateUsing(fn ($state) => '$' . number_format($state, 2, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
