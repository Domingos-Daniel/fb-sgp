<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramaSocialResource\Pages;
use App\Filament\Resources\ProgramaSocialResource\RelationManagers;
use App\Filament\Resources\ProgramaSocialResource\RelationManagers\SubprogramasRelationManager;
use App\Models\OrcamentoGeral;
use App\Models\ProgramaSocial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
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
                Forms\Components\Grid::make(2)->schema([ // Criação de um layout em grid de 2 colunas
                    Forms\Components\TextInput::make('titulo')
                        ->label('Título do Programa')
                        ->required()
                        ->unique(fn(string $context): bool => $context === 'create')
                        ->maxLength(255)
                        ->placeholder('Digite o título do programa')
                        ->prefixIcon('heroicon-o-pencil') // Ícone prefixado
                        ->columnSpan(2), // Toma as duas colunas no grid

                    Forms\Components\RichEditor::make('descricao')
                        ->label('Descrição')
                        ->required()
                        ->placeholder('Descreva o programa...')
                        ->columnSpanFull(), // Toma o espaço completo do formulário

                    Forms\Components\TextInput::make('publico_alvo')
                        ->label('Público Alvo')
                        ->required()
                        ->maxLength(255)
                        ->placeholder('Digite o público-alvo')
                        ->prefixIcon('heroicon-o-users'), // Ícone prefixado

                    Forms\Components\RichEditor::make('meta')
                        ->label('Meta do Programa')
                        ->required()
                        ->placeholder('Descreva a meta do programa...')
                        ->columnSpanFull(), // Toma o espaço completo do formulário
                    // Campo para selecionar o orçamento geral aprovado
                    Forms\Components\Select::make('id_orcamento')
                        ->label('Orçamento Geral Aprovado')
                        ->options(function () {
                            return OrcamentoGeral::where('status', 'Aprovado')
                                ->get()
                                ->pluck('display_name', 'id');
                        })
                        ->reactive() // Permite que o campo reaja a alterações de estado
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                // Busca o orçamento geral selecionado e define o valor restante como estado
                                $orcamentoGeral = OrcamentoGeral::find($state);
                                if ($orcamentoGeral) {
                                    $set('valor_orcamento_programa_restante', $orcamentoGeral->valor_restante);
                                }
                            }
                        })
                        ->searchable()
                        ->required()
                        ->placeholder('Selecione um orçamento aprovado')
                        ->columnSpan(1),

                    // Campo para definir o valor do orçamento do programa
                    Forms\Components\TextInput::make('valor')
                        ->label('Valor do Orçamento para o Programa (USD)')
                        ->numeric()
                        ->required()
                        ->columnSpan(1)
                        ->reactive() // Permite que o campo reaja a alterações de estado
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            $orcamentoId = $get('id_orcamento'); // Obtém o valor do orçamento selecionado
                            $orcamentoGeral = OrcamentoGeral::find($orcamentoId);

                            if ($orcamentoGeral && $state > $orcamentoGeral->valor_restante) {
                                // Se o valor inserido for maior que o valor restante, exibir mensagem de erro e redefinir o valor
                                $set('valor', null);
                                Notification::make()
                                    ->title('Erro no Formulário')
                                    ->body("O valor inserido excede o valor restante do orçamento geral (USD " . number_format($orcamentoGeral->valor_restante, 2, ',', '.') . ").")
                                    ->danger()
                                    ->send();
                            }
                        }),

                    // Campo somente de visualização para mostrar o valor restante do orçamento geral
                    Forms\Components\TextInput::make('valor_orcamento_programa_restante')
                        ->label('Valor Restante do Orçamento Geral (USD)')
                        ->numeric()
                        ->disabled()
                        ->default('0.00')
                        ->columnSpan(1),
                    Forms\Components\Hidden::make('id_criador')
                        ->default(auth()->user()->id),
                ]),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('titulo')
                    ->label('Título do Programa')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-briefcase') // Ícone
                    ->weight('bold') // Texto em negrito
                    ->toggleable(),

                Tables\Columns\TextColumn::make('publico_alvo')
                    ->label('Público Alvo')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary') // Cor do badge
                    ->icon('heroicon-o-users'),

                Tables\Columns\TextColumn::make('id_criador')
                    ->label('Criado por')
                    ->sortable()
                    ->formatStateUsing(function ($state) {
                        return \App\Models\User::find($state)?->name ?? '-'; // Retorna o nome do criador a partir do id
                    })
                    ->icon('heroicon-o-user-circle')
                    ->color('secondary'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->icon('heroicon-o-calendar')
                    ->color('info')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i')
                    ->icon('heroicon-o-clock')
                    ->color('info')
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Visualizar')
                    ->icon('heroicon-o-eye')
                    ->color('info'),

                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil')
                    ->color('primary'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->label('Excluir Selecionados')
                    ->icon('heroicon-o-trash')
                    ->color('danger'),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
{
    return $infolist
        ->schema([
            Section::make('Informações do Programa')
                ->schema([
                    TextEntry::make('titulo')
                        ->label('Título do Programa')
                        ->badge()
                        ->color('success')
                        ->icon('heroicon-o-briefcase')
                        ->weight('bold'),

                    TextEntry::make('descricao')
                        ->label('Descrição')
                        ->html()
                        ->columnSpanFull(),

                    TextEntry::make('publico_alvo')
                        ->label('Público Alvo')
                        ->badge()
                        ->color('primary')
                        ->icon('heroicon-o-users'),

                    TextEntry::make('meta')
                        ->label('Meta do Programa')
                        ->html()
                        ->columnSpanFull(),
                ])
                ->columns(2) // Layout em duas colunas
                ->columnSpanFull(),

            Section::make('Informações de Orçamento')
                ->schema([
                    TextEntry::make('orcamento_geral')
                        ->label('Orçamento Geral Associado')
                        ->icon('heroicon-o-currency-dollar')
                        ->getStateUsing(function ($record) {
                            // Verifica se o programa possui orçamento associado
                            $orcamentoPrograma = $record->orcamentoPrograma;

                            if ($orcamentoPrograma) {
                                // Busca o orçamento geral usando o id_orcamento na tabela orcamentoprograma
                                $orcamentoGeral = \App\Models\OrcamentoGeral::find($orcamentoPrograma->id_orcamento);
                                return $orcamentoGeral ? "Orçamento #{$orcamentoGeral->id} - USD " . number_format($orcamentoGeral->valor_total, 2, ',', '.') : '- Não Associado';
                            }

                            return '- Não Associado';
                        }),

                    TextEntry::make('orcamento_programa.valor')
                        ->label('Valor do Orçamento para o Programa (USD)')
                        ->icon('heroicon-o-currency-dollar')
                        ->getStateUsing(function ($record) {
                            // Verifica se o programa possui orçamento associado e retorna o valor do orçamento
                            return optional($record->orcamentoPrograma)->valor
                                ? 'USD $' . number_format($record->orcamentoPrograma->valor, 2, ',', '.')
                                : '- Não Definido';
                        }),
                ])
                ->columns(2) // Layout em duas colunas
                ->columnSpanFull(),

            Section::make('Outras Informações')
                ->schema([
                    TextEntry::make('criador.name')
                        ->label('Criado por')
                        ->badge()
                        ->color('secondary')
                        ->icon('heroicon-o-user-circle'),

                    TextEntry::make('created_at')
                        ->label('Criado em')
                        ->dateTime('d/m/Y H:i')
                        ->icon('heroicon-o-calendar')
                        ->color('info'),

                    TextEntry::make('updated_at')
                        ->label('Atualizado em')
                        ->dateTime('d/m/Y H:i')
                        ->icon('heroicon-o-clock')
                        ->color('info'),
                ])
                ->columns(2) // Layout em duas colunas
                ->columnSpanFull(),
        ]);
}


    public static function getRelations(): array
    {
        return [
            //
            SubprogramasRelationManager::class,
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
