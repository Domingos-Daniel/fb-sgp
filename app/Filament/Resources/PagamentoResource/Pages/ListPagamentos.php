<?php

namespace App\Filament\Resources\PagamentoResource\Pages;

use App\Filament\Resources\PagamentoResource;
use App\Helpers\DateHelper;
use App\Models\Pagamento;
use App\Models\Patrocinio;
use App\Models\Subprograma;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Collection;

class ListPagamentos extends ListRecords
{
    protected static string $resource = PagamentoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('gerarPagamentosMassa')
                ->label('Gerar Pagamentos em Massa')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->modalWidth('4xl')
                ->modalHeading('Geração de Pagamentos em Massa')
                ->modalDescription('Selecione um subprograma e gere pagamentos para múltiplos beneficiários de uma só vez.')
                ->form([
                    Tabs::make('Subprogramas')
                        ->tabs(function () {
                            $tabs = [];
                            
                            // Busca todos os subprogramas ativos com patrocínios elegíveis
                            $subprogramas = Subprograma::whereHas('patrocinios', function ($query) {
                                $query->where('status', 'ativo')
                                    ->whereDoesntHave('pagamentos', function ($q) {
                                        $q->where('created_at', '>=', now()->startOfQuarter());
                                    });
                            })->get();
                            
                            foreach ($subprogramas as $subprograma) {
                                // Conta beneficiários elegíveis para este subprograma
                                $patrociniosElegiveis = Patrocinio::where('status', 'ativo')
                                    ->where('id_subprograma', $subprograma->id)
                                    ->whereDoesntHave('pagamentos', function ($query) {
                                        $query->where('created_at', '>=', now()->startOfQuarter());
                                    })
                                    ->count();
                                
                                if ($patrociniosElegiveis > 0) {
                                    $tabs[] = Tab::make("sub_{$subprograma->id}")
                                        ->label("{$subprograma->descricao} ({$patrociniosElegiveis})")
                                        ->schema([
                                            Card::make()
                                                ->schema([
                                                    Placeholder::make('info_subprograma')
                                                        ->label('Informações do Subprograma')
                                                        ->content(function () use ($subprograma) {
                                                            return "Valor por beneficiário: USD " . number_format($subprograma->valor, 2, ',', '.') . 
                                                                " | Periodicidade: {$subprograma->tipo_pagamento}";
                                                        }),
                                                        
                                                    DatePicker::make("data_pagamento_{$subprograma->id}")
                                                        ->label('Data do Pagamento')
                                                        ->required()
                                                        ->default(now())
                                                        ->reactive()
                                                        ->afterStateUpdated(function ($state, callable $set) use ($subprograma) {
                                                            if ($state) {
                                                                // Initialize $proximoData here
                                                                $proximoData = Carbon::parse($state);
                                                                
                                                                switch (strtolower($subprograma->tipo_pagamento)) {
                                                                    case 'mensal':
                                                                        $proximoData->addMonth();
                                                                        break;
                                                                    case 'trimestral':
                                                                        $proximoData->addMonths(3);
                                                                        break;
                                                                    case 'semestral':
                                                                        $proximoData->addMonths(6);
                                                                        break;
                                                                    case 'anual':
                                                                        $proximoData->addYear();
                                                                        break;
                                                                    default:
                                                                        $proximoData->addMonth();
                                                                }
                                                                
                                                                $set("proximo_pagamento_{$subprograma->id}", $proximoData->format('Y-m-d'));
                                                            }
                                                        }),

                                                    Placeholder::make("proximo_pagamento_{$subprograma->id}")
                                                        ->label('Previsão do Próximo Pagamento')
                                                        ->content(function (callable $get) use ($subprograma) {
                                                            try {
                                                                $dataPagamento = $get("data_pagamento_{$subprograma->id}");
                                                                if (empty($dataPagamento)) return 'Data não definida';
                                                                
                                                                // Assume formato padrão do Filament
                                                                $proximoData = now();
                                                                
                                                                // Calcula próxima data baseado no tipo de pagamento
                                                                switch (strtolower($subprograma->tipo_pagamento)) {
                                                                    case 'mensal':
                                                                        return 'Próximo mês';
                                                                    case 'trimestral':
                                                                        return 'Próximo trimestre';
                                                                    case 'semestral':
                                                                        return 'Próximo semestre';
                                                                    case 'anual':
                                                                        return 'Próximo ano';
                                                                    default:
                                                                        return 'Próximo período';
                                                                }
                                                            } catch (\Exception $e) {
                                                                return 'Não foi possível calcular';
                                                            }
                                                        })
                                                        ->helperText('Baseado na periodicidade do subprograma')
                                                        ->extraAttributes(['class' => 'text-primary-600 font-medium']),
                                                        
                                                    Textarea::make("observacao_{$subprograma->id}")
                                                        ->label('Observação (opcional)')
                                                        ->placeholder('Adicione uma observação para este lote de pagamentos')
                                                        ->maxLength(255),
                                                        
                                                    Repeater::make("beneficiarios_{$subprograma->id}")
                                                        ->label('Beneficiários Elegíveis')
                                                        ->schema([
                                                            Toggle::make('selecionado')
                                                                ->label('Selecionar')
                                                                ->default(true)
                                                                ->inline(false),
                                                                
                                                            // Substitua os Placeholders por TextInput desabilitados
                                                            TextInput::make('nome')
                                                                ->label('Beneficiário')
                                                                ->disabled(),
                                                                
                                                            TextInput::make('iban')
                                                                ->label('IBAN')
                                                                ->disabled(),
                                                                
                                                            TextInput::make('valor')
                                                                ->label('Valor (USD)')
                                                                ->formatStateUsing(fn ($state) => number_format($state, 2, ',', '.'))
                                                                ->disabled(),
                                                        ])
                                                        ->columns(4)
                                                        ->default(function () use ($subprograma) {
                                                            $beneficiarios = [];
                                                            
                                                            $patrocinios = Patrocinio::where('status', 'ativo')
                                                                ->where('id_subprograma', $subprograma->id)
                                                                ->whereDoesntHave('pagamentos', function ($query) {
                                                                    $query->where('created_at', '>=', now()->startOfQuarter());
                                                                })
                                                                ->with(['beneficiario', 'subprograma'])
                                                                ->get();
                                                                
                                                            foreach ($patrocinios as $patrocinio) {
                                                                $beneficiarios[] = [
                                                                    'id' => $patrocinio->id,
                                                                    'selecionado' => true,
                                                                    'nome' => $patrocinio->beneficiario->nome,
                                                                    'iban' => $patrocinio->beneficiario->coordenadas_bancarias ?? 'Não informado',
                                                                    'valor' => $subprograma->valor,
                                                                ];
                                                            }
                                                            
                                                            return $beneficiarios;
                                                        })
                                                        ->disableItemCreation()
                                                        ->disableItemDeletion()
                                                        ->disableItemMovement(),
                                                        
                                                    Grid::make(2)
                                                        ->schema([
                                                            Placeholder::make("total_beneficiarios_{$subprograma->id}")
                                                                ->label('Total de Beneficiários')
                                                                ->content(function () use ($patrociniosElegiveis) {
                                                                    return $patrociniosElegiveis;
                                                                }),
                                                                
                                                            Placeholder::make("valor_total_{$subprograma->id}")
                                                                ->label('Valor Total (USD)')
                                                                ->content(function () use ($subprograma, $patrociniosElegiveis) {
                                                                    $total = $subprograma->valor * $patrociniosElegiveis;
                                                                    return number_format($total, 2, ',', '.');
                                                                }),
                                                        ]),
                                                        
                                                    Toggle::make("selecionar_todos_{$subprograma->id}")
                                                        ->label('Selecionar/Desselecionar Todos')
                                                        ->default(true)
                                                        ->live()
                                                        ->afterStateUpdated(function ($state, callable $set, callable $get) use ($subprograma) {
                                                            $beneficiarios = $get("beneficiarios_{$subprograma->id}");
                                                            
                                                            if (is_array($beneficiarios)) {
                                                                foreach ($beneficiarios as $key => $beneficiario) {
                                                                    $beneficiarios[$key]['selecionado'] = $state;
                                                                }
                                                                
                                                                $set("beneficiarios_{$subprograma->id}", $beneficiarios);
                                                            }
                                                        }),
                                                ]),
                                        ]);
                                }
                            }
                            
                            // Se não houver subprogramas com beneficiários elegíveis
                            if (empty($tabs)) {
                                $tabs[] = Tab::make('sem_beneficiarios')
                                    ->label('Sem Beneficiários Elegíveis')
                                    ->schema([
                                        Placeholder::make('aviso')
                                            ->label('Nenhum beneficiário disponível para pagamento')
                                            ->content('Todos os beneficiários com patrocínios ativos já receberam pagamento neste período.')
                                            ->extraAttributes(['class' => 'text-warning-600 font-medium']),
                                    ]);
                            }
                            
                            return $tabs;
                        }),
                ])
                ->action(function (array $data) {
                    $pagamentosCriados = 0;
                    $valorTotal = 0;
                    $subprogramaProcessado = null;
                    
                    // Processa cada subprograma
                    foreach ($data as $key => $value) {
                        if (str_starts_with($key, 'beneficiarios_')) {
                            // Obter ID do subprograma a partir da chave
                            $subprogramaId = (int) str_replace(['beneficiarios_', 'sub_'], '', $key);
                            $subprograma = Subprograma::find($subprogramaId);
                            
                            if ($subprograma) {
                                $subprogramaProcessado = $subprograma;
                                // Usar a data diretamente
                                $dataPagamento = $data["data_pagamento_{$subprograma->id}"];
                                $observacao = $data["observacao_{$subprograma->id}"] ?? '';
                                
                                // Processa cada beneficiário selecionado
                                foreach ($value as $beneficiarioData) {
                                    if ($beneficiarioData['selecionado'] ?? false) {
                                        $patrocinioId = $beneficiarioData['id'];
                                        
                                        // Valor padrão do subprograma (mais seguro)
                                        $valorPagamento = $subprograma->valor;
                                        
                                        // Cria o pagamento
                                        Pagamento::create([
                                            'id_patrocinio' => $patrocinioId,
                                            'data_pagamento' => $dataPagamento,
                                            'valor' => $valorPagamento,
                                            'status' => 'pendente',
                                            'observacoes' => $observacao ?: 'Pagamento gerado em massa',
                                            'id_criador' => auth()->id(),
                                        ]);
                                        
                                        $pagamentosCriados++;
                                        $valorTotal += $valorPagamento;
                                    }
                                }
                            }
                        }
                    }
                    
                    // Exibe notificação com resumo
                    if ($pagamentosCriados > 0 && $subprogramaProcessado) {
                        Notification::make()
                            ->title("Pagamentos gerados com sucesso")
                            ->body("{$pagamentosCriados} pagamentos criados para o subprograma '{$subprogramaProcessado->descricao}', totalizando USD " . number_format($valorTotal, 2, ',', '.'))
                            ->success()
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('view')
                                    ->button()
                                    ->label('Ver Pagamentos')
                                    ->url(route('filament.admin.resources.pagamentos.index'))
                            ])
                            ->send();
                    } else {
                        Notification::make()
                            ->title("Nenhum pagamento gerado")
                            ->body("Nenhum beneficiário foi selecionado ou todos já receberam pagamento no período atual.")
                            ->warning()
                            ->send();
                    }
                }),
                
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
