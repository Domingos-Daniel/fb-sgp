<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BeneficiarioResource\Pages;
use App\Filament\Resources\BeneficiarioResource\RelationManagers;
use App\Models\Beneficiario;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BeneficiarioResource extends Resource
{
    protected static ?string $model = Beneficiario::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Tipo de Beneficiário')
                        ->schema([
                            Forms\Components\Select::make('tipo_beneficiario')
                                ->label('Tipo de Beneficiário')
                                ->options([
                                    'Individual' => 'Individual',
                                    'Institucional' => 'Institucional',
                                ])
                                ->required()
                                ->native(false)
                                ->reactive()
                                ->afterStateUpdated(fn (callable $set) => $set('is_individual', fn ($state) => $state === 'Individual'))
                                ->default('Individual'),
                        ])
                        ->columns(1),
                    
                    Wizard\Step::make('Informações Pessoais ou Institucionais')
                        ->schema([
                            Grid::make(2)->schema([
                                Forms\Components\TextInput::make('nome')
                                    ->label(fn (Get $get) => $get('is_individual') ? 'Nome Completo' : 'Nome da Instituição')
                                    ->required()
                                    ->maxLength(255),
                            
                                Forms\Components\TextInput::make('bi')
                                    ->label('BI')
                                    ->maxLength(14)
                                    ->minLength(14)
                                    //->default('Não Aplicável')
                                    ->visible(fn (Get $get) => $get('tipo_beneficiario') === 'Individual'),
                            
                                Forms\Components\TextInput::make('nif')
                                    ->label('NIF da Instituição')
                                    ->maxLength(14)
                                    ->minLength(14)
                                    //->default('Não Aplicável')
                                    ->visible(fn (Get $get) => $get('tipo_beneficiario') === 'Institucional'),
                            ]),
                            
                            Grid::make(2)->schema([
                                Forms\Components\DatePicker::make('data_nascimento')
                                    ->label('Data de Nascimento')
                                    ->native(false)
                                    ->required(fn (Get $get) => $get('tipo_beneficiario') === 'Individual')
                                    ->rule('before_or_equal:today')
                                    ->visible(fn (Get $get) => $get('tipo_beneficiario') === 'Individual'),
    
                                Forms\Components\Radio::make('genero')
                                    ->label('Gênero')
                                    ->options([
                                        'Masculino' => 'Masculino',
                                        'Feminino' => 'Feminino',
                                        'Outro' => 'Outro',
                                    ])
                                    ->required(fn (Get $get) => $get('tipo_beneficiario') === 'Individual')
                                    ->visible(fn (Get $get) => $get('tipo_beneficiario') === 'Individual'),
                            ]),
                            
                            Grid::make(2)->schema([
                                Forms\Components\TextInput::make('email')
                                    ->label(fn (Get $get) => $get('is_individual') ? 'Email Pessoal' : 'Email da Instituição')
                                    ->required()
                                    ->email()
                                    ->maxLength(255),
    
                                Forms\Components\TextInput::make('telemovel')
                                    ->label(fn (Get $get) => $get('is_individual') ? 'Telemóvel Pessoal' : 'Telemóvel da Instituição')
                                    ->tel()
                                    ->numeric()
                                    ->required()
                                    ->maxLength(14),
    
                                Forms\Components\TextInput::make('telemovel_alternativo')
                                    ->label('Telemóvel Alternativo')
                                    ->tel()
                                    ->numeric()
                                    ->maxLength(14),
                            ]),
                        ])
                        ->columns(1),
                    
                    Wizard\Step::make('Outras Informações')
                        ->schema([
                            Grid::make(2)->schema([
                                Forms\Components\TextInput::make('endereco')
                                    ->required()
                                    ->prefixIcon('heroicon-o-map-pin')
                                    ->label(fn (Get $get) => $get('is_individual') ? 'Endereço Pessoal' : 'Endereço da Instituição')
                                    ->maxLength(255),
                            
                                Forms\Components\TextInput::make('pais')
                                    ->label('País')
                                    ->required()
                                    ->prefixIcon('heroicon-o-flag')
                                    ->maxLength(255)
                                    ->default('Angola'),
    
                                Forms\Components\Select::make('provincia')
                                    ->label('Província')
                                    ->required()
                                    ->prefixIcon('heroicon-o-map')
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
                                    
                            
                                Forms\Components\TextInput::make('coordenadas_bancarias')
                                    ->label(fn (Get $get) => $get('is_individual') ? 'Coordenadas Pessoais' : 'Coordenadas da Instituição')
                                    ->prefix('AO06 ')
                                    ->maxLength(255)
                                    ->required()
                                    ->default('Não Aplicável')
                                    ,
                            ]),
                            
                            Grid::make(2)->schema([
                                Forms\Components\TextInput::make('ano_frequencia')
                                    ->label('Ano de Frequência')
                                    ->maxLength(255)
                                    ->default('Não Aplicável')
                                    ->visible(fn (Get $get) => $get('tipo_beneficiario') === 'Individual'),
    
                                Forms\Components\TextInput::make('curso')
                                    ->label('Curso')
                                    ->maxLength(255)
                                    ->default('Não Aplicável')
                                    ->visible(fn (Get $get) => $get('tipo_beneficiario') === 'Individual'),
    
                                Forms\Components\TextInput::make('universidade_ou_escola')
                                    ->label('Universidade ou Escola')
                                    ->maxLength(255)
                                    ->default('Não Aplicável')
                                    ->visible(fn (Get $get) => $get('tipo_beneficiario') === 'Individual'),
                            ]),
                            
                            Forms\Components\RichEditor::make('observacoes')
                                ->label('Observações')
                                ->columnSpanFull(),
                            
                            Forms\Components\Hidden::make('id_criador')
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('nome')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bi')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nif')
                    ->searchable(),
                Tables\Columns\TextColumn::make('data_nascimento')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('genero')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telemovel')
                    ->searchable(),
                Tables\Columns\TextColumn::make('telemovel_alternativo')
                    ->searchable(),
                Tables\Columns\TextColumn::make('endereco')
                    ->searchable(),
                Tables\Columns\TextColumn::make('pais')
                    ->searchable(),
                Tables\Columns\TextColumn::make('coordenadas_bancarias')
                    ->searchable(),
                Tables\Columns\TextColumn::make('ano_frequencia')
                    ->searchable(),
                Tables\Columns\TextColumn::make('curso')
                    ->searchable(),
                Tables\Columns\TextColumn::make('universidade_ou_escola')
                    ->searchable(),
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
            'index' => Pages\ListBeneficiarios::route('/'),
            'create' => Pages\CreateBeneficiario::route('/create'),
            'view' => Pages\ViewBeneficiario::route('/{record}'),
            'edit' => Pages\EditBeneficiario::route('/{record}/edit'),
        ];
    }
}
