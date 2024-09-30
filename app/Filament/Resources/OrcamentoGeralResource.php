<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrcamentoGeralResource\Pages;
use App\Filament\Resources\OrcamentoGeralResource\RelationManagers;
use App\Filament\Resources\OrcamentoGeralResource\RelationManagers\ProgramasRelationManager;
use App\Models\OrcamentoGeral;
use App\Models\User;
use App\Notifications\OrcamentoAprovadoNotification;
use App\Notifications\OrcamentoPendenteNotification;
use App\Notifications\OrcamentoReprovadoNotification;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action as ActionsAction;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Notification as FacadesNotification;

class OrcamentoGeralResource extends Resource
{
    protected static ?string $model = OrcamentoGeral::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Orçamento Geral';
    protected static ?string $pluralModelLabel  = 'Orçamentos Gerais';
    protected static ?string $navigationGroup = 'Administração';
    protected static ?string $recordTitleAttribute = 'valor_total';

    protected function getWorkflowParticipants($record)
    {
        $participants = collect();

        // Adiciona o DG se estiver envolvido
        if ($record->valor_total < 500000 || $record->workflow->etapa == 1) {
            $dgUsers = User::role('DG')->get();
            $participants = $participants->merge($dgUsers);
        }

        // Adiciona o CA Curadores se estiver envolvido
        if ($record->valor_total >= 500000 && $record->workflow->etapa >= 2) {
            $caCuradores = User::role('CA Curadores')->get();
            $participants = $participants->merge($caCuradores);
        }

        // Remove o usuário atual para evitar enviar notificação a si mesmo
        $participants = $participants->filter(function ($user) {
            return $user->id !== auth()->id();
        });

        return $participants;
    }


    public static function form(Form $form): Form
    {
        $currentYear = date('Y');
        $semesters = [
            "{$currentYear} - 1º Semestre" => "{$currentYear} - 1º Semestre",
            "{$currentYear} - 2º Semestre" => "{$currentYear} - 2º Semestre",
        ];

        return $form
            ->schema([
                Grid::make(2) // Organização em colunas para melhor layout visual
                    ->schema([
                        TextInput::make('valor_total')
                            ->label('Valor Total (USD)')
                            ->numeric()
                            ->unique()
                            ->prefix('USD') // Prefixo do valor
                            ->placeholder('Digite o valor total')
                            ->required()
                            ->helperText('Informe o valor total do orçamento.')
                            ->columnSpan(1),

                        Select::make('ano_semestre')
                            ->label('Ano/Semestre')
                            ->options($semesters)
                            ->searchable() // Permite busca
                            ->required()
                            ->helperText('Selecione o ano e semestre para este orçamento.')
                            ->placeholder('Selecione o ano e semestre')
                            ->native(false) // Usar a estilização customizada do Filament
                            ->columnSpan(1),
                    ]),

                Grid::make(2)
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'Pendente' => 'Pendente',
                            ])
                            ->default('Pendente') // Valor padrão
                            ->required()
                            ->disabled()
                            ->helperText('Status atual do orçamento.')
                            ->columnSpan(1),

                        RichEditor::make('observacoes')
                            ->label('Observações')
                            ->required()
                            ->unique()
                            ->helperText('Alguma observação para este orçamento.'),

                        Hidden::make('id_criador')
                            ->default(auth()->user()->id)
                            ->helperText('ID do usuário que criou o orçamento.')
                            ->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Coluna com formatação de valor e ícone
                TextColumn::make('valor_total')
                    ->label('Valor Total (USD)')
                    ->formatStateUsing(fn($state) => '$' . number_format($state, 2, ',', '.')) // Formato de valor com duas casas decimais
                    ->icon('heroicon-o-currency-dollar') // Ícone para indicar valor
                    ->iconColor('primary') // Cor do ícone
                    ->sortable()
                    ->tooltip(fn($state) => "Valor total do orçamento: $ {$state}")
                    ->badge()
                    ->color('info') // Cor da badge
                    ->extraAttributes(['class' => 'font-bold text-lg']), // Estilo extra para a coluna

                // Coluna de Ano/Semestre com badge
                BadgeColumn::make('ano_semestre')
                    ->label('Ano/Semestre')
                    ->colors([
                        'primary' => fn($state) => str_contains($state, '1º Semestre'),
                        'secondary' => fn($state) => str_contains($state, '2º Semestre'),
                    ])
                    ->sortable()
                    ->searchable()
                    ->tooltip(fn($state) => "Ano e semestre do orçamento: {$state}"),

                // Coluna de Status com badge e cores
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'Aprovado',
                        'warning' => 'Pendente',
                        'danger' => 'Rejeitado',
                    ])
                    ->sortable()
                    ->searchable()
                    ->icon(fn($state) => match ($state) {
                        'Aprovado' => 'heroicon-o-check-circle',
                        'Pendente' => 'heroicon-o-exclamation-circle',
                        'Rejeitado' => 'heroicon-o-x-circle',
                        default => null,
                    })
                    ->iconPosition('before') // Ícone antes do texto
                    ->tooltip(fn($state) => "Status do orçamento: {$state}"),

                // Coluna de Criador, exibindo o nome ao invés do ID
                TextColumn::make('id_criador')
                    ->label('Criado por')
                    ->getStateUsing(fn($record) => optional(User::find($record->id_criador))->name ?? 'Desconhecido')
                    ->tooltip(fn($record) => "Criado por: " . (User::find($record->id_criador)->name ?? 'Desconhecido'))
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-user-circle') // Ícone indicando o criador
                    ->iconColor('success') // Cor do ícone

                    // Cor extra da badge com base no tipo de usuário que criou
                    ->badge()
                    ->color(fn($state) => match (optional(User::find($state))->role ?? '') {
                        'DG' => 'success',
                        'CA Curadores' => 'warning',
                        default => 'secondary',
                    }),

                // Colunas de Data e Hora, escondidas por padrão
                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('aprovar')
                    ->label('Aprovar')
                    ->icon('heroicon-o-check-circle')
                    ->color('success') // Estilo success
                    ->modalButton('Aprovar')
                    ->modalHeading('Aprovar Orçamento')
                    ->modalSubheading('Deseja realmente aprovar este orçamento?')
                    ->modalIcon('heroicon-o-check-circle')
                    ->modalIconColor('success')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('observacoes')
                            ->label('Observações')
                            ->placeholder('Insira observações (opcional)'),
                    ])
                    ->action(function ($record, array $data) {
                        $user = auth()->user();

                        // Aqui passamos o método getWorkflowParticipants como uma closure
                        $getWorkflowParticipants = function ($record) {
                            $participants = collect();

                            // Adiciona o DG se estiver envolvido
                            if ($record->valor_total < 500000 || $record->workflow->etapa == 1) {
                                $dgUsers = User::role('DG')->get();
                                $participants = $participants->merge($dgUsers);
                            }

                            // Adiciona o CA Curadores se estiver envolvido
                            if ($record->valor_total >= 500000 && $record->workflow->etapa >= 2) {
                                $caCuradores = User::role('CA Curadores')->get();
                                $participants = $participants->merge($caCuradores);
                            }

                            // Remove o usuário atual para evitar enviar notificação a si mesmo
                            $participants = $participants->filter(function ($user) {
                                return $user->id !== auth()->id();
                            });

                            return $participants;
                        };

                        if (!$user->canApprove($record)) {
                            Notification::make()
                                ->title('Ação Não Permitida')
                                ->body('Você não tem permissão para aprovar este orçamento.')
                                ->danger()
                                ->persistent()
                                ->send();
                            return;
                        }

                        $workflow = $record->workflow;

                        if (!$workflow) {
                            Notification::make()
                                ->title('Erro')
                                ->body('Workflow não encontrado para este orçamento.')
                                ->danger()
                                ->persistent()
                                ->send();
                            return;
                        }

                        // Atualiza o workflow
                        $workflow->status = 'Aprovado';
                        $workflow->aprovador_id = $user->id;
                        $workflow->data_aprovacao = now();
                        $workflow->observacoes = $data['observacoes'] ?? null;
                        $workflow->save();

                        // Verifica se precisa passar para a próxima etapa
                        if ($record->valor_total >= 500000 && $workflow->etapa == 1) {
                            // Inicia a próxima etapa
                            $workflow->status = 'Pendente';
                            $workflow->etapa = 2;
                            $workflow->aprovador_id = null;
                            $workflow->data_aprovacao = null;
                            $workflow->observacoes = null;
                            $workflow->save();

                            // Notifica o CA Curadores
                            $caCuradores = User::role('CA Curadores')->get();
                            Notification::make()
                                ->title('Orçamento Pendente de Aprovação')
                                ->body("O orçamento #{$record->id} foi aprovado pelo DG e está pendente de sua aprovação.")
                                ->persistent()
                                ->sendToDatabase($caCuradores)
                                ->send();

                            Notification::make()
                                ->title('Orçamento Enviado para Próxima Etapa')
                                ->body('O orçamento foi aprovado e enviado para o CA Curadores.')
                                ->success()
                                ->persistent()
                                ->send();
                        } else {
                            // Aprovação final
                            $record->status = 'Aprovado';
                            $record->save();

                            Notification::make()
                                ->title('Orçamento Aprovado')
                                ->body("O orçamento #{$record->id} foi aprovado com sucesso.")
                                ->success()
                                ->persistent()
                                ->send();

                            // Notificar o criador
                            Notification::make()
                                ->title('Orçamento Aprovado')
                                ->body("Seu orçamento #{$record->id} foi aprovado.")
                                ->success()
                                ->persistent()
                                ->sendToDatabase($record->criador)
                                ->send();

                            // Utiliza a closure para obter os participantes do workflow
                            $participants = $getWorkflowParticipants($record);
                            Notification::make()
                                ->title('Orçamento Aprovado')
                                ->body("O orçamento #{$record->id} foi aprovado.")
                                ->success()
                                ->persistent()
                                ->sendToDatabase($participants)
                                ->send();
                        }
                    })
                    ->visible(fn($record) => $record->workflow->status === 'Pendente' && auth()->user()->canApprove($record)),

                Action::make('reprovar')
                    ->label('Reprovar')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger') // Estilo danger
                    ->modalButton('Reprovar')
                    ->modalHeading('Reprovar Orçamento')
                    ->modalSubheading('Deseja realmente reprovar este orçamento? Por favor, forneça o motivo.')
                    ->modalIcon('heroicon-o-x-circle')
                    ->modalIconColor('danger')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Textarea::make('observacoes')
                            ->label('Motivo da Reprovação')
                            ->required()
                            ->placeholder('Descreva o motivo da reprovação'),
                    ])
                    ->action(function ($record, array $data) {
                        $user = auth()->user();

                        // Encapsular o método `getWorkflowParticipants` como uma closure
                        $getWorkflowParticipants = function ($record) {
                            $participants = collect();

                            // Adiciona o DG se estiver envolvido
                            if ($record->valor_total < 500000 || $record->workflow->etapa == 1) {
                                $dgUsers = User::role('DG')->get();
                                $participants = $participants->merge($dgUsers);
                            }

                            // Adiciona o CA Curadores se estiver envolvido
                            if ($record->valor_total >= 500000 && $record->workflow->etapa >= 2) {
                                $caCuradores = User::role('CA Curadores')->get();
                                $participants = $participants->merge($caCuradores);
                            }

                            // Remove o usuário atual para evitar enviar notificação a si mesmo
                            $participants = $participants->filter(function ($user) {
                                return $user->id !== auth()->id();
                            });

                            return $participants;
                        };

                        if (!$user->canApprove($record)) {
                            Notification::make()
                                ->title('Ação Não Permitida')
                                ->body('Você não tem permissão para reprovar este orçamento.')
                                ->danger()
                                ->persistent()
                                ->send();
                            return;
                        }

                        $workflow = $record->workflow;

                        if (!$workflow) {
                            Notification::make()
                                ->title('Erro')
                                ->body('Workflow não encontrado para este orçamento.')
                                ->danger()
                                ->persistent()
                                ->send();
                            return;
                        }

                        // Atualiza o workflow
                        $workflow->status = 'Rejeitado';
                        $workflow->aprovador_id = $user->id;
                        $workflow->data_aprovacao = now();
                        $workflow->observacoes = $data['observacoes'];
                        $workflow->save();

                        // Atualiza o orçamento
                        $record->status = 'Rejeitado';
                        $record->save();

                        Notification::make()
                            ->title('Orçamento Reprovado')
                            ->body("O orçamento #{$record->id} foi reprovado.")
                            ->danger()
                            ->persistent()
                            ->send();

                        // Notificar o criador com o motivo
                        Notification::make()
                            ->title('Orçamento Reprovado')
                            ->body("Seu orçamento #{$record->id} foi reprovado. Motivo: {$data['observacoes']}")
                            ->danger()
                            ->persistent()
                            ->sendToDatabase($record->criador)
                            ->send();

                        // Utiliza a closure para obter os participantes do workflow
                        $participants = $getWorkflowParticipants($record);
                        Notification::make()
                            ->title('Orçamento Reprovado')
                            ->body("O orçamento #{$record->id} foi reprovado.")
                            ->danger()
                            ->persistent()
                            ->sendToDatabase($participants)
                            ->send();
                    })
                    ->visible(fn($record) => $record->workflow->status === 'Pendente' && auth()->user()->canApprove($record)),

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
            // Seção para os detalhes do orçamento
            Section::make('Detalhes do Orçamento')
                ->columns(2) // Organiza os elementos em duas colunas
                ->schema([
                    TextEntry::make('valor_total')
                        ->label('Valor Total (USD)')
                        ->formatStateUsing(fn ($state) => '$' . number_format($state, 2, ',', '.'))
                        ->icon('heroicon-o-currency-dollar')
                        ->iconPosition('before')
                        ->extraAttributes(['class' => 'font-bold text-lg']),

                    TextEntry::make('ano_semestre')
                        ->label('Ano/Semestre')
                        ->badge()
                        ->color('primary'), // Badge colorida

                    TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->color(fn ($state) => match ($state) {
                            'Aprovado' => 'success',
                            'Pendente' => 'warning',
                            'Rejeitado' => 'danger',
                            default => 'secondary',
                        })
                        ->icon(fn ($state) => match ($state) {
                            'Aprovado' => 'heroicon-o-check-circle',
                            'Pendente' => 'heroicon-o-exclamation-circle',
                            'Rejeitado' => 'heroicon-o-x-circle',
                            default => null,
                        })
                        ->iconPosition('before'),

                    TextEntry::make('criador.name')
                        ->label('Criado por')
                        ->icon('heroicon-o-user-circle')
                        ->iconPosition('before')
                        ->badge()
                        ->color('success'),

                    TextEntry::make('created_at')
                        ->label('Criado em')
                        ->badge()
                        ->color('info')
                        ->dateTime('d/m/Y H:i:s'),
                ]),
        ]);
}

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrcamentoProgramasRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrcamentoGerals::route('/'),
            'create' => Pages\CreateOrcamentoGeral::route('/create'),
            'view' => Pages\ViewOrcamentoGeral::route('/{record}'),
            'edit' => Pages\EditOrcamentoGeral::route('/{record}/edit'),
        ];
    }
}
