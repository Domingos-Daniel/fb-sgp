<?php

namespace App\Filament\Resources\SubprogramaResource\RelationManagers;

use App\Models\Beneficiario;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PatrociniosRelationManager extends RelationManager
{
    protected static string $relationship = 'patrocinios';
    protected static ?string $recordTitleAttribute = 'id';

    public function form(Form $form): Form
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



                Forms\Components\DatePicker::make('data_inicio')
                    ->label('Data de Início')
                    ->required()
                    ->default(now()->format('Y-m-d')),

                Forms\Components\DatePicker::make('data_fim')
                    ->label('Data de Fim')
                    ->required()
                    ->default(fn($get) => now()->addMonths($this->getSubprogramaDuracao($get('id_subprograma')))),

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

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
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
