<?php

namespace App\Filament\Resources;

use App\Filament\Exports\BeneficiarioExporter;
use App\Filament\Resources\BeneficiarioResource\Pages;
use App\Filament\Resources\BeneficiarioResource\RelationManagers;
use App\Filament\Resources\BeneficiarioResource\RelationManagers\PagamentosRelationManager;
use App\Filament\Resources\BeneficiarioResource\RelationManagers\PatrociniosRelationManager;
use App\Filament\Resources\BeneficiarioResource\Widgets\BeneficiarioStatsOverview;
use App\Models\Beneficiario;
use App\Models\Patrocinio;
use App\Models\Subprograma;
use Carbon\Carbon;
use Closure;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\GlobalSearch\Actions\Action;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Illuminate\Support\Str;
use Filament\Tables\Actions\Action as TAction;
use Filament\Tables\Actions\ExportBulkAction;
use Illuminate\Database\Eloquent\Collection;

class BeneficiarioResource extends Resource
{
    protected static ?string $model = Beneficiario::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $recordTitleAttribute = 'nome';

    protected static ?string $modelLabel = 'Beneficiario';
    //protected static ?int $navigationSort = 1;
    protected static ?string $pluralModelLabel = 'Beneficiarios';

    protected static ?string $navigationGroup = 'Gestão de Beneficiários';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Wizard::make([
                    // Passo 1: Tipo de Beneficiário
                    Wizard\Step::make('Tipo de Beneficiário')
                        ->schema([
                            FileUpload::make('imagem')
                                ->label("Imagem do Beneficiário (PNG, JPG, etc.)")
                                ->image()
                                ->avatar()
                                ->disk('public')
                                ->acceptedFileTypes(['image/*'])
                                ->maxSize(1024) // 1MB
                                ->directory('beneficiarios')
                                ->imageEditor()
                                ->imageEditorMode(2)
                                ->rules(
                                    'max:1024',
                                    'mimetypes:image/jpeg,image/png,image/jpg'
                                )
                                ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file): string {
                                    $count = Beneficiario::count() + 1; // Conta o número de registros e incrementa 1
                                    $extension = $file->getClientOriginalExtension();
                                    $filename = Str::padLeft($count, 4, '0'); // Gera um número com 4 dígitos, com zeros à esquerda
                                    return "fb{$filename}.{$extension}";
                                }),

                            Select::make('tipo_beneficiario')
                                ->label('Tipo de Beneficiário')
                                ->options([
                                    'Individual' => 'Individual',
                                    'Institucional' => 'Institucional',
                                ])
                                ->required()
                                ->native(false)
                                ->reactive() // Garantir que o campo é reativo
                                ->default('Individual'),
                        ])
                        ->columns(1),

                    // Passo 2: Informações Pessoais ou Institucionais
                    Wizard\Step::make('Informações Pessoais ou Institucionais')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('nome')
                                    ->label(fn($get) => $get('tipo_beneficiario') === 'Individual' ? 'Nome Completo' : 'Nome da Instituição')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('bi')
                                    ->label('BI')
                                    ->unique()
                                    ->maxLength(14)
                                    ->minLength(14)
                                    ->required()
                                    ->visible(fn($get) => $get('tipo_beneficiario') === 'Individual'),

                                TextInput::make('nif')
                                    ->label('NIF da Instituição')
                                    ->maxLength(14)
                                    ->unique()
                                    ->minLength(14)
                                    ->required()
                                    ->visible(fn($get) => $get('tipo_beneficiario') === 'Institucional'),
                            ]),

                            Grid::make(2)->schema([
                                DatePicker::make('data_nascimento')
                                    ->label('Data de Nascimento')
                                    ->native(false)
                                    ->required(fn($get) => $get('tipo_beneficiario') === 'Individual')
                                    ->rule('before_or_equal:today')
                                    ->visible(fn($get) => $get('tipo_beneficiario') === 'Individual'),

                                Radio::make('genero')
                                    ->label('Gênero')
                                    ->options([
                                        'Masculino' => 'Masculino',
                                        'Feminino' => 'Feminino',
                                        'Outro' => 'Outro',
                                    ])
                                    ->required(fn($get) => $get('tipo_beneficiario') === 'Individual')
                                    ->visible(fn($get) => $get('tipo_beneficiario') === 'Individual'),
                            ]),

                            Grid::make(2)->schema([
                                TextInput::make('email')
                                    ->label(fn($get) => $get('tipo_beneficiario') === 'Individual' ? 'Email Pessoal' : 'Email da Instituição')
                                    ->required()
                                    ->email()
                                    ->maxLength(255),

                                TextInput::make('telemovel')
                                    ->label(fn($get) => $get('tipo_beneficiario') === 'Individual' ? 'Telemóvel Pessoal' : 'Telemóvel da Instituição')
                                    ->tel()
                                    ->numeric()
                                    ->required()
                                    ->maxLength(14),

                                TextInput::make('telemovel_alternativo')
                                    ->label('Telemóvel Alternativo')
                                    ->tel()
                                    ->numeric()
                                    ->maxLength(14),
                            ]),
                        ])
                        ->columns(1),

                    // Passo 3: Outras Informações
                    Wizard\Step::make('Outras Informações')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('endereco')
                                    ->required()
                                    ->prefixIcon('heroicon-o-map-pin')
                                    ->label(fn($get) => $get('tipo_beneficiario') === 'Individual' ? 'Endereço Pessoal' : 'Endereço da Instituição')
                                    ->maxLength(255),

                                TextInput::make('pais')
                                    ->label('País')
                                    ->required()
                                    ->prefixIcon('heroicon-o-flag')
                                    ->maxLength(255)
                                    ->default('Angola'),

                                Select::make('provincia')
                                    ->label('Província')
                                    ->required()
                                    ->prefixIcon('heroicon-o-map')
                                    ->searchable()
                                    ->native(false)
                                    ->options([
                                        'Luanda' => 'Luanda',
                                        'Huambo' => 'Huambo',
                                        'Benguela' => 'Benguela',
                                        'Namibe' => 'Namibe',
                                        'Moxico' => 'Moxico',
                                        'Moxico Leste' => 'Moxico Leste',
                                        'Zaire' => 'Zaire',
                                        'Cuanza Norte' => 'Cuanza Norte',
                                        'Cuanza Sul' => 'Cuanza Sul',
                                        'Bengo' => 'Bengo',
                                        'Icolo e Bengo' => 'Icolo e Bengo',
                                        'Bié' => 'Bié',
                                        'Malanje' => 'Malanje',
                                        'Huila' => 'Huila',
                                        'Cunene' => 'Cunene',
                                        'Cuando' => 'Cuando',
                                        'Cubango' => 'Cubango',
                                        'Lunda Norte' => 'Lunda Norte',
                                        'Lunda Sul' => 'Lunda Sul',
                                        'Uige' => 'Uige',
                                        'Cabinda' => 'Cabinda',
                                    ])
                                    ->default('Luanda'),
                            ]),

                            Grid::make(2)->schema([
                                TextInput::make('coordenadas_bancarias')
                                    ->label(fn($get) => $get('tipo_beneficiario') === 'Individual' ? 'Coordenadas Pessoais' : 'Coordenadas da Instituição')
                                    ->prefix('AO06 ')
                                    ->maxLength(255)
                                    ->required(),

                                TextInput::make('ano_frequencia')
                                    ->label('Ano de Frequência')
                                    ->maxLength(255)
                                    ->visible(fn($get) => $get('tipo_beneficiario') === 'Individual'),

                                TextInput::make('curso')
                                    ->label('Curso')
                                    ->maxLength(255)
                                    ->visible(fn($get) => $get('tipo_beneficiario') === 'Individual'),

                                TextInput::make('universidade_ou_escola')
                                    ->label('Universidade ou Escola')
                                    ->maxLength(255)
                                    ->visible(fn($get) => $get('tipo_beneficiario') === 'Individual'),
                            ]),

                            RichEditor::make('observacoes')
                                ->label('Observações')
                                ->columnSpanFull(),

                            Hidden::make('id_criador')
                                ->label('ID do Criador')
                                ->required()
                                ->default(auth()->user()->id),
                        ])
                        ->columns(1),
                ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tipo_beneficiario')
                    ->label('Tipo')
                    ->badge()
                    ->colors([
                        'primary' => 'Individual',
                        'info' => 'Institucional',
                    ])
                    ->icons([
                        'heroicon-o-user' => 'Individual',
                        'heroicon-o-building-office' => 'Institucional',
                    ])
                    ->searchable(),

                Tables\Columns\TextColumn::make('nome')
                    ->label('Nome')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('bi')
                    ->label('BI')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('nif')
                    ->label('NIF')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('data_nascimento')
                    ->label('Data de Nascimento')
                    ->date('d/m/Y') // Formato DD/MM/YYYY
                    ->sortable()
                    ->badge()
                    ->color('primary')
                    ->icon('heroicon-o-calendar')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('genero')
                    ->label('Gênero')
                    ->badge()
                    ->colors([
                        'danger' => 'Feminino',
                        'info' => 'Masculino',
                        'gray' => 'Outro',
                    ])
                    ->icons([
                        'heroicon-o-user-circle' => fn($state): bool => $state === 'Masculino' || $state === 'Feminino',
                        'heroicon-o-question-mark-circle' => fn($state): bool => $state === 'Outro',
                    ])
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->icon('heroicon-o-envelope'),

                Tables\Columns\TextColumn::make('telemovel')
                    ->label('Telemóvel')
                    ->searchable()
                    ->icon('heroicon-o-phone'),

                Tables\Columns\TextColumn::make('endereco')
                    ->label('Endereço')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-map-pin'),

                Tables\Columns\TextColumn::make('pais')
                    ->label('País')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-flag'),

                Tables\Columns\TextColumn::make('provincia')
                    ->label('Província')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-map'),

                Tables\Columns\TextColumn::make('ano_frequencia')
                    ->label('Ano de Frequência')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-academic-cap'),

                Tables\Columns\TextColumn::make('curso')
                    ->label('Curso')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-book-open'),

                Tables\Columns\TextColumn::make('universidade_ou_escola')
                    ->label('Universidade ou Escola')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->icon('heroicon-o-office-building'),

                // Tables\Columns\TextColumn::make('id_criador')
                //     ->label('Criador')
                //     ->numeric()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true)
                //     ->icon('heroicon-o-user-group'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-clock'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado em')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->icon('heroicon-o-arrow-path')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Defina os filtros, se necessário
                //
                Tables\Filters\Filter::make('created_at')
                    ->label('Intervalo de Data de Criação')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Intervalo de Data de Criação Inicio'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Intervalo de Data de Criação Fim'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),

                Tables\Filters\Filter::make('data_nascimento')
                    ->label('Data de Nascimento')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Data de Nascimento Inicial'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Data de Nascimento Final'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('data_nascimento', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('data_nascimento', '<=', $date),
                            );
                    }),

                Tables\Filters\SelectFilter::make('tipo_beneficiario')
                    ->label('Tipo de Beneficiário')
                    ->multiple()
                    ->options([
                        'Individual' => 'Individual',
                        'Institucional' => 'Institucional',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                TAction::make('exportPdf')
                    ->label('Exportar PDF')
                    ->color('danger')
                    ->icon('heroicon-o-document-text')
                    ->action(function ($record) {
                        return redirect()->route('beneficiarios.export-pdf', $record->id);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make('export')
                        ->label('Exportar')
                        ->exporter(BeneficiarioExporter::class)
                        ->columnMapping(true)
                        ->formats([
                            ExportFormat::Xlsx,
                            ExportFormat::Csv,
                        ]),
                ]),
                Tables\Actions\BulkAction::make('atribuirPatrocinio')
                    ->label('Atribuir Patrocínio')
                    ->icon('heroicon-o-banknotes')
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('id_subprograma')
                                    ->label('Subprograma')
                                    ->options(function () {
                                        return Subprograma::all()
                                            ->mapWithKeys(function ($subprograma) {
                                                $duracao = $subprograma->duracao_patrocinio ? " ({$subprograma->duracao_patrocinio} meses)" : "";
                                                return [$subprograma->id => "{$subprograma->descricao}{$duracao}"];
                                            });
                                    })
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state) {
                                            $subprograma = Subprograma::find($state);
                                            if ($subprograma) {
                                                // Define a data de início como hoje
                                                $dataInicio = now()->format('Y-m-d');
                                                $set('data_inicio', $dataInicio);
                                                
                                                // Calcula a data de fim
                                                if ($subprograma->duracao_patrocinio) {
                                                    $dataFim = now()->addMonths($subprograma->duracao_patrocinio)->format('Y-m-d');
                                                    $set('data_fim', $dataFim);
                                                }
                                                
                                                // Armazena a duração para facilitar recálculos
                                                $set('duracao_meses', $subprograma->duracao_patrocinio);
                                            }
                                        }
                                    }),

                                Forms\Components\TextInput::make('duracao_meses')
                                    ->label('Duração (meses)')
                                    ->disabled()
                                    ->suffixIcon('heroicon-o-calendar')
                                    ->visible(function (Get $get) {
                                        return $get('id_subprograma') !== null;
                                    }),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\DatePicker::make('data_inicio')
                                    ->label('Data de Início')
                                    ->required()
                                    ->default(now())
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Recalcula data de fim quando mudar a data de início
                                        $duracao = $get('duracao_meses');
                                        if ($duracao && $state) {
                                            $dataFim = Carbon::parse($state)->addMonths($duracao)->format('Y-m-d');
                                            $set('data_fim', $dataFim);
                                        }
                                    }),
                                
                                Forms\Components\DatePicker::make('data_fim')
                                    ->label('Data de Fim')
                                    ->required()
                                    ->readonly()
                                    ->reactive(),
                            ]),
                            
                        Forms\Components\Textarea::make('observacoes')
                            ->label('Observações')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->action(function (Collection $records, array $data) {
                        $count = 0;
                        
                        foreach ($records as $beneficiario) {
                            // Verifica se já tem um patrocínio ativo
                            if (!$beneficiario->patrocinios()->where('status', 'ativo')->exists()) {
                                Patrocinio::create([
                                    'id_beneficiario' => $beneficiario->id,
                                    'id_subprograma' => $data['id_subprograma'],
                                    'status' => 'ativo',
                                    'data_inicio' => $data['data_inicio'],
                                    'data_fim' => $data['data_fim'],
                                    'observacoes' => $data['observacoes'] ?? null,
                                    'id_criador' => auth()->id(),
                                ]);
                                $count++;
                            }
                        }
                        
                        Notification::make()
                            ->title("{$count} patrocínios criados com sucesso")
                            ->success()
                            ->send();
                    }),
            ]);
    }


    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informações Básicas')
                    ->columns(2)
                    ->schema([

                        ImageEntry::make('imagem')
                            ->label('Imagem do Beneficiário')
                            ->square()
                            ->circular()
                            ->disk('public')
                            ->default('beneficiarios/default.png')
                            ->columnSpanFull()
                            ->extraImgAttributes([
                                'alt' => 'Imagem do Beneficiário',
                                'loading' => 'lazy',
                            ])
                            ->width('100px')
                            ->height('100px'),

                        TextEntry::make('nome')
                            ->label('Nome')
                            ->badge()
                            ->color('info'),


                        TextEntry::make('tipo_beneficiario')
                            ->label('Tipo de Beneficiário')
                            ->badge()
                            ->colors([
                                'info' => 'Individual',
                                'success' => 'Institucional',
                            ])
                            ->icons([
                                'heroicon-o-user' => 'Individual',
                                'heroicon-o-building-office' => 'Institucional',
                            ]),

                        TextEntry::make('bi')
                            ->label('BI')
                            ->badge()
                            ->icon('heroicon-o-identification')
                            ->color('info')
                            ->hidden(fn($record) => $record->tipo_beneficiario !== 'Individual'),

                        TextEntry::make('nif')
                            ->label('NIF')
                            ->badge()
                            ->icon('heroicon-o-identification')
                            ->color('info')
                            ->hidden(fn($record) => $record->tipo_beneficiario !== 'Institucional'),

                        TextEntry::make('data_nascimento')
                            ->label('Data de Nascimento')
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-o-calendar-date-range')
                            ->date('d/m/Y')
                            ->hidden(fn($record) => $record->tipo_beneficiario !== 'Individual'),

                        TextEntry::make('genero')
                            ->label('Gênero')
                            ->badge()
                            ->colors([
                                'info' => 'Masculino',
                                'success' => 'Feminino',
                                'warning' => 'Outro',
                            ])
                            ->hidden(fn($record) => $record->tipo_beneficiario !== 'Individual'),
                    ]),

                Section::make('Contatos e Localização')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('email')
                            ->label('Email')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-envelope'),

                        TextEntry::make('telemovel')
                            ->label('Telemóvel')
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-o-phone'),

                        TextEntry::make('telemovel_alternativo')
                            ->label('Telemóvel Alternativo')
                            ->badge()
                            ->color('success')
                            ->icon('heroicon-o-phone'),

                        TextEntry::make('endereco')
                            ->label('Endereço')
                            ->badge()
                            ->color('info')
                            ->icon('heroicon-o-map-pin')
                            ->hidden(fn($record) => $record->tipo_beneficiario !== 'Institucional'),

                        TextEntry::make('pais')
                            ->label('País')
                            ->badge()
                            ->color('danger')
                            ->icon('heroicon-o-flag'),

                        TextEntry::make('provincia')
                            ->label('Província')
                            ->badge()
                            ->color('warning')
                            ->icon('heroicon-o-map'),
                    ]),

                Section::make('Educação e Profissão')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('ano_frequencia')
                            ->label('Ano de Frequência')
                            ->hidden(fn($record) => $record->tipo_beneficiario !== 'Individual'),

                        TextEntry::make('curso')
                            ->label('Curso')
                            ->hidden(fn($record) => $record->tipo_beneficiario !== 'Individual'),

                        TextEntry::make('universidade_ou_escola')
                            ->label('Universidade ou Escola')
                            ->hidden(fn($record) => $record->tipo_beneficiario !== 'Individual'),
                    ]),

                Section::make('Outras Informações')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('observacoes')
                            ->label('Observações')
                            ->html(),

                        TextEntry::make('coordenadas_bancarias')
                            ->label('Coordenadas Bancárias')
                            ->hidden(fn($record) => $record->tipo_beneficiario !== 'Institucional'),

                        TextEntry::make('created_at')
                            ->label('Criado em')
                            ->dateTime('d M Y, H:i')
                            ->icon('heroicon-o-clock'),

                        TextEntry::make('updated_at')
                            ->label('Atualizado em')
                            ->dateTime('d M Y, H:i')
                            ->icon('heroicon-o-arrow-path'),
                    ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            //
            PagamentosRelationManager::class,
            PatrociniosRelationManager::class,
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string | Htmlable
    {
        return $record->nome;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['nome', 'email', 'endereco', 'telemovel', 'telemovel_alternativo', 'bi', 'nif'];
    }

    public static function getGlobalSearchResultActions(Model $record): array
    {
        return [
            Action::make('view')
                ->label('Visualizar')
                ->color('warning')
                ->url(static::getUrl('view', ['record' => $record])),

            Action::make('edit')
                ->label('Editar')
                ->color('info')
                ->url(static::getUrl('edit', ['record' => $record])),
        ];
    }


    public static function getWidgets(): array
    {
        return [
            BeneficiarioStatsOverview::class,
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
