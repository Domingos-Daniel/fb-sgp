<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubprogramaResource\Pages;
use App\Filament\Resources\SubprogramaResource\RelationManagers;
use App\Models\Gasto;
use App\Models\ProgramaSocial;
use App\Models\Subprograma;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Infolists\Components;
use Filament\Support\Enums\FontWeight;
use Filament\Infolists\Infolist;
use Closure;

class SubprogramaResource extends Resource
{
    protected static ?string $model = Subprograma::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Subprogramas';
    protected static ?string $pluralModelLabel  = 'Subprogramas';

    protected static ?string $navigationGroup = 'Gestão de Programas Sociais';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            // Campo Select reativo para selecionar o programa social
            Forms\Components\Select::make('id_programa')
                ->label("Selecione o Programa Social")
                ->options(ProgramaSocial::pluck('titulo', 'id')->toArray())
                ->searchable()
                ->live()
                ->native(false)
                ->preload()
                ->required(fn (string $context): bool => $context === 'create'),

            // Campo Select reativo para orçamento disponível
            Forms\Components\Select::make('orcamento_id')
                ->label('Orçamento Disponível')
                ->suffixIcon('heroicon-m-banknotes')
                ->suffixIconColor('success')
                ->hiddenOn('edit')
                ->options(function (Get $get) {
                    $id_programa = $get('id_programa');

                    if (!$id_programa) {
                        return [];
                    }

                    $programa = ProgramaSocial::find($id_programa);

                    if (!$programa) {
                        return [];
                    }

                    $valorGastoPrograma = Gasto::where('id_programa', $id_programa)->sum('valor_gasto');
                    $valorOrcamento = $programa->orcamento;
                    $valorDisponivel = $valorOrcamento - $valorGastoPrograma;

                    return [$id_programa => 'Kz ' . number_format($valorDisponivel, 2, ',', '.')];
                })
                ->reactive()
                ->default(function (Get $get) {
                    $id_programa = $get('id_programa');

                    if (!$id_programa) {
                        return null;
                    }

                    $programa = ProgramaSocial::find($id_programa);
                    $valorGastoPrograma = Gasto::where('id_programa', $id_programa)->sum('valor_gasto');
                    $valorOrcamento = $programa->orcamento;

                    return 'Kz ' . number_format($valorOrcamento - $valorGastoPrograma, 2, ',', '.');
                })
                ->disabled()
                ->selectablePlaceholder(false),

            // Campo de entrada de texto para a designação do subprograma
            Forms\Components\TextInput::make('descricao')
                ->label("Designação")
                ->unique(ignoreRecord: true)
                ->required(fn (string $context): bool => $context === 'create')
                ->maxLength(255),

            // Campo de entrada de texto para o valor do subprograma
            Forms\Components\TextInput::make('valor')
                ->label('Valor do Subprograma')
                ->required(fn (string $context): bool => $context === 'create')
                ->numeric()
                ->reactive()
                ->rules([
                    fn (Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                        $programaId = $get('id_programa');
                        $programa = ProgramaSocial::find($programaId);

                        if (!$programa) {
                            $fail("Programa não encontrado.");
                            return;
                        }

                        $valorGasto = Gasto::where('id_programa', $programaId)->sum('valor_gasto');
                        $orcamentoDisponivel = $programa->orcamento - $valorGasto;

                        if ((float) $value > $orcamentoDisponivel) {
                            $fail("O valor inserido para o subprograma é maior que o orçamento disponível.");
                        } elseif ((float) $value == $orcamentoDisponivel) {
                            $fail("O valor inserido para o subprograma é igual ao orçamento disponível.");
                        }
                    },
                ]),

            // Campo oculto para o ID do criador
            Forms\Components\Hidden::make('id_criador')
                ->default(auth()->id()),
        ]);
}


    // No seu recurso Laravel Nova, você pode adicionar um método estático para calcular a diferença
    public static function calcularDiferenca($programaId)
{
    // Acessar o orçamento do programa
    $orcamento = ProgramaSocial::where('id', $programaId)->pluck('orcamento')->first();

    // Somar os gastos do programa
    $gastos = Gasto::where('id_programa', $programaId)->sum('valor_gasto');

    // Calcular a diferença entre o orçamento total e os gastos
    $diferenca = $orcamento - $gastos;

    return $diferenca;
}



public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('programaSocial.titulo')
                ->label('Programa Social'),

            Tables\Columns\TextColumn::make('descricao')
                ->label('Designação')
                ->searchable(),

            Tables\Columns\TextColumn::make('valor')
                ->label('Valor do Subprograma')
                ->money('USD', true)
                ->sortable(),

            Tables\Columns\TextColumn::make('orcamentoDisponivel')
                ->label('Orçamento Disponível')
                ->getStateUsing(function ($record) {
                    $programa = $record->programaSocial;
                    $valorGasto = Gasto::where('id_programa', $record->id_programa)->sum('valor_gasto');
                    return $programa->orcamento - $valorGasto;
                })
                ->money('USD', true)
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
            Components\Section::make([
                Components\TextEntry::make('descricao')
                    ->label('Designação')
                    ->badge(),

                Components\TextEntry::make('programaSocial.titulo')
                    ->label('Programa Social')
                    ->badge(),

                Components\TextEntry::make('valor')
                    ->label('Valor do Subprograma')
                    ->money('USD', true)
                    ->badge()
                    ->color(fn ($record) => $record->valor < 1000 ? 'danger' : 'success'),

                Components\TextEntry::make('orcamentoDisponivel')
                    ->label('Orçamento Disponível')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $programa = $record->programaSocial;
                        $valorGasto = Gasto::where('id_programa', $record->id_programa)->sum('valor_gasto');
                        return $programa->orcamento - $valorGasto;
                    })
                    ->money('USD', true)
                    ->color(fn ($record) => $record->programaSocial->orcamento - Gasto::where('id_programa', $record->id_programa)->sum('valor_gasto') < 1000 ? 'danger' : 'success'),
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
            'index' => Pages\ListSubprogramas::route('/'),
            'create' => Pages\CreateSubprograma::route('/create'),
            'view' => Pages\ViewSubprograma::route('/{record}'),
            'edit' => Pages\EditSubprograma::route('/{record}/edit'),
        ];
    }
}
