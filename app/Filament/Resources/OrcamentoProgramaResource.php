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
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
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
                        ->afterStateUpdated(fn(callable $set) => $set('id_orcamento', null)), // Reseta o orçamento ao mudar o programa

                    Forms\Components\Select::make('id_orcamento')
                        ->label('Orçamento Aprovado')
                        ->options(function () {
                            // Filtrar apenas os orçamentos que possuem o status exatamente como 'Aprovado'
                            return OrcamentoGeral::whereRaw("TRIM(status) = ?", ['Aprovado'])
                            ->get()
                            ->pluck('valor_total', 'id')
                            ->map(fn ($valor) => '$' . number_format($valor, 2, ',', '.'))
                            ->toArray();
                        }) // Certifique-se de que a relação 'orcamento' está definida no modelo OrcamentoPrograma
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
                            fn(Get $get): Closure => function (string $attribute, $value, Closure $fail) use ($get) {
                                $valorInserido = (float) $value;
                                $orcamentoSelecionado = OrcamentoGeral::find($get('id_orcamento'));

                                if ($orcamentoSelecionado) {
                                    $valorRestante = $orcamentoSelecionado->valor_total - $orcamentoSelecionado->valor_alocado;

                                    // Verificar se o valor inserido é maior que o restante
                                    if ($valorInserido > $valorRestante) {
                                        $fail("O valor inserido excede o valor restante do orçamento ($valorRestante).");
                                    } else if ($valorInserido <= 0) {
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
                        ->formatStateUsing(fn($state) => '$' . number_format($state, 2, ',', '.')) // Formato do valor restante
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
                Tables\Columns\TextColumn::make('programa.titulo')
                    ->label('Programa Social')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-building-office')
                    ->weight(FontWeight::Bold)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('orcamento.valor_total')
                    ->label('Valor do Orçamento (USD)')
                    ->sortable()
                    ->formatStateUsing(fn($state) => '$' . number_format($state, 2, ',', '.'))
                    ->icon('heroicon-o-currency-dollar')
                    ->iconColor('primary')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('valor')
                    ->label('Valor Atribuído ao Programa (USD)')
                    ->sortable()
                    ->formatStateUsing(fn($state) => '$' . number_format($state, 2, ',', '.'))
                    ->color('info')
                    ->icon('heroicon-m-banknotes'),

                // Valor Restante do Orçamento, acessando através da relação
                Tables\Columns\TextColumn::make('orcamento.valor_restante')
                    ->label('Valor Restante do Orçamento (USD)')
                    ->sortable()
                    ->getStateUsing(fn($record) => $record->orcamento ? $record->orcamento->valor_restante : 0)
                    ->formatStateUsing(fn($state) => '$' . number_format($state, 2, ',', '.'))
                    ->color(fn($state) => $state < 10000 ? 'danger' : 'success') // Cor com base no valor restante
                    ->icon('heroicon-m-check-circle')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('criador.name')
                    ->label('Criado por')
                    ->searchable()
                    ->icon('heroicon-o-user')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informações do Programa')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('programa.titulo')
                            ->label('Programa Social')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('orcamento.valor_total')
                            ->label('Valor Total do Orçamento (USD)')
                            ->badge()
                            ->color('info')
                            ->formatStateUsing(fn($state) => '$' . number_format($state, 2, ',', '.')),

                        TextEntry::make('valor')
                            ->label('Valor Atribuído (USD)')
                            ->badge()
                            ->color('success')
                            ->formatStateUsing(fn($state) => '$' . number_format($state, 2, ',', '.')),

                        // Valor Restante do Orçamento, acessando através da relação
                        TextEntry::make('orcamento.valor_restante')
                            ->label('Valor Restante (USD)')
                            ->badge()
                            ->color(fn($state) => $state < 20000 ? 'danger' : 'success')
                            ->getStateUsing(fn($record) => $record->orcamento ? $record->orcamento->valor_restante : 0)
                            ->formatStateUsing(fn($state) => '$' . number_format($state, 2, ',', '.')),

                        TextEntry::make('criador.name')
                            ->label('Criado por')
                            ->icon('heroicon-o-user-circle')
                            ->iconPosition('before')
                            ->badge()
                            ->color('info'),

                        TextEntry::make('created_at')
                            ->label('Criado em')
                            ->dateTime('d/m/Y H:i:s'),
                    ]),

                Section::make('Observações')
                    ->schema([
                        TextEntry::make('observacoes')
                            ->label('Observações')
                            ->html(), // Caso seja um campo rich text
                    ])
                    ->columnSpanFull(),
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
