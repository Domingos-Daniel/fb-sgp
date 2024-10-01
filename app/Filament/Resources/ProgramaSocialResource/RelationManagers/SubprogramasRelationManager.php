<?php

namespace App\Filament\Resources\ProgramaSocialResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubprogramasRelationManager extends RelationManager
{
    protected static string $relationship = 'subprogramas';
    protected static bool $isLazy = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('descricao')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('descricao')
            ->columns([
                TextColumn::make('descricao')
                    ->label('Designação')
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('valor')
                    ->label('Valor (USD)')
                    ->badge()
                    ->color('info')
                    ->money('usd', true)

                    ->sortable(),

                TextColumn::make('tipo_pagamento')
                    ->label('Tipo de Pagamento')
                    ->badge()
                    ->color('primary')
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
