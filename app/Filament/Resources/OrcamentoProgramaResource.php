<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrcamentoProgramaResource\Pages;
use App\Filament\Resources\OrcamentoProgramaResource\RelationManagers;
use App\Models\OrcamentoGeral;
use App\Models\OrcamentoPrograma;
use App\Models\ProgramaSocial;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrcamentoProgramaResource extends Resource
{
    protected static ?string $model = OrcamentoPrograma::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Orçamento Programa';
    protected static ?string $pluralModelLabel  = 'Orçamentos Programas';
    protected static ?string $navigationGroup = 'Administração';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\Select::make('id_programa')
                    ->label('Programa Social')
                    ->relationship('programa', 'titulo') // Certifique-se de que a relação 'programa' está definida no modelo OrcamentoPrograma
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->placeholder('Selecione um Programa')
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set) => $set('id_orcamento', null)), // Reseta o orçamento ao mudar o programa

                Forms\Components\Select::make('id_orcamento')
                    ->label('Orçamento Aprovado')
                    ->options(function () {
                        // Apenas os orçamentos aprovados
                        return OrcamentoGeral::where('status', 'Aprovado')
                            ->pluck('valor_total', 'id')
                            ->map(fn ($valor) => '$' . number_format($valor, 2, ',', '.')) // Formato do valor
                            ->toArray();
                    })
                    ->relationship('orcamento', 'valor_total') // Certifique-se de que a relação 'orcamento' está definida no modelo OrcamentoPrograma
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->placeholder('Selecione um Orçamento')
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            $orcamento = OrcamentoGeral::find($state);
                            $valorRestante = $orcamento ? $orcamento->valor_total - $orcamento->valor_alocado : 0;
                            $set('valor_restante', $valorRestante);
                        }
                    }),

                Forms\Components\TextInput::make('valor')
                    ->label('Valor Atribuído ao Programa (USD)')
                    ->numeric()
                    ->required()
                    ->rules([
                        fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                            $valorInserido = (float) $value;
                            $orcamentoSelecionado = OrcamentoGeral::find($get('id_orcamento'));

                            if ($orcamentoSelecionado) {
                                $valorRestante = $orcamentoSelecionado->valor_total - $orcamentoSelecionado->valor_alocado;

                                // Verificar se o valor inserido é maior que o restante
                                if ($valorInserido > $valorRestante) {
                                    $fail("O valor inserido excede o valor restante do orçamento ($valorRestante).");
                                }else if ($valorInserido <= 0) {
                                    $fail("O valor inserido é inválido, por favor, insira um valor maior que zero.");
                                }
                            }
                        },
                    ]),

                // Campo de visualização para o valor restante do orçamento
                Forms\Components\TextInput::make('valor_restante')
                    ->label('Valor Restante do Orçamento (USD)')
                    ->disabled()
                    ->numeric()
                    ->formatStateUsing(fn ($state) => '$' . number_format($state, 2, ',', '.')) // Formato do valor restante
                    ->default(0)
                    ->prefix('Restante: '),

                // Campo hidden para ID do criador
                Forms\Components\Hidden::make('id_criador')
                    ->default(auth()->id()),
            ]),
        ]);
}

    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id_programa')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_orcamento')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('valor')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status'),
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
            'index' => Pages\ListOrcamentoProgramas::route('/'),
            'create' => Pages\CreateOrcamentoPrograma::route('/create'),
            'view' => Pages\ViewOrcamentoPrograma::route('/{record}'),
            'edit' => Pages\EditOrcamentoPrograma::route('/{record}/edit'),
        ];
    }
}
