<?php

namespace App\Filament\Resources;

use App\Exports\UsersExport;
use App\Filament\Exports\UserExporter;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\ExportBulkAction as ActionsExportBulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Rmsramos\Activitylog\RelationManagers\ActivitylogRelationManager;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\Filter;
use Rmsramos\Activitylog\Actions\ActivityLogTimelineTableAction;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Administração';
    protected static ?string $modelLabel = 'Utilizador';
    protected static ?string $pluralModelLabel = 'Utilizadores';


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                //Forms\Components\DateTimePicker::make('email_verified_at'),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->dehydrateStateUsing(fn($state) => Hash::make($state))
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context): bool => $context === 'create')
                    ->maxLength(255)
                    ->label("Palavra Passe"),
                Forms\Components\Select::make('roles')
                    ->label("Função")
                    ->hint("Selecione uma ou mais funções para o utilizador")
                    ->relationship('roles', 'name', function (Builder $query) {
                        return auth()->user()->hasRole('Admin') ? $query : $query->where('name', '!=', 'Admin');
                    })
                    ->multiple()
                    ->native(false)
                    ->searchable()
                    ->required(fn(string $context): bool => $context === 'create')
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label("Nome Completo"),
                Tables\Columns\TextColumn::make('email')
                    ->label("Email")
                    ->badge()
                    ->color('success')
                    ->searchable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Função')
                    ->searchable()
                    ->badge()
                    ->color(
                        fn($record) => $record->roles->pluck('name')->first() === "Admin" ? "success" : "info",
                    )
                    ->sortable(),
                // Tables\Columns\TextColumn::make('email_verified_at')
                //     ->dateTime(format: "d/m/Y H:i:s")
                //     ->sortable()
                //     ->label("Data Validado"),
                Tables\Columns\TextColumn::make('created_at')
                    ->label("Data Criado")
                    ->dateTime(format: "d/m/Y H:i:s")
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label("Data Atualizado")
                    ->dateTime(format: "d/m/Y H:i:s")
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->options(Role::all()->pluck('name', 'id'))
                    ->label('Função')
                    ->multiple()
                    ->placeholder('Pesquisar funções...'),

                Filter::make('created_at')
                    ->label('Data de Criação')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Data de Criação Inicial')
                            ->placeholder('Selecione a data inicial')
                            ->closeOnDateSelection(),
                        DatePicker::make('created_until')
                            ->label('Data de Criação Final')
                            ->placeholder('Selecione a data final')
                            ->closeOnDateSelection(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'A partir de ' . \Carbon\Carbon::parse($data['created_from'])->format('d/m/Y');
                        }

                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Até ' . \Carbon\Carbon::parse($data['created_until'])->format('d/m/Y');
                        }

                        return $indicators;
                    }),

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                ActivityLogTimelineTableAction::make('Activities')
                    ->color('warning')
                    ->label('Actividades')
                    ->timelineIcons([
                        'created' => 'heroicon-m-check-badge',
                        'updated' => 'heroicon-m-pencil-square',
                    ])
                    ->timelineIconColors([
                        'created' => 'info',
                        'updated' => 'warning',
                    ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ExportBulkAction::make('export')
                        ->label('Exportar')
                        ->exporter(UserExporter::class)
                        ->columnMapping(true)
                        ->formats([
                            ExportFormat::Xlsx,
                            ExportFormat::Csv,
                        ]),

                ]),
            ]);
    }



    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Components\Split::make([
                    // Seção de Informações Pessoais
                    Components\Section::make('Informações Pessoais')
                        ->schema([
                            Components\TextEntry::make('name')
                                ->badge()
                                ->label('Nome Completo')
                                ->weight(FontWeight::Bold)
                                ->color('info'),
                            Components\TextEntry::make('email')
                                ->badge()
                                ->label('Email')
                                ->color('success'),
                            Components\TextEntry::make('roles.name')
                                ->badge()
                                ->label('Função')
                                ->color(
                                    fn($record) => $record->roles->pluck('name')->first() === "Admin" ? "success" : "info",
                                ),
                        ])->grow(true),
                ]),

                // Seção de Datas
                Components\Split::make([
                    Components\Section::make('Datas Importantes')->schema([
                        Components\TextEntry::make('email_verified_at')
                            ->badge()
                            ->label('Data Validado')
                            ->color('info'),
                        Components\TextEntry::make('created_at')
                            ->badge()
                            ->label('Data Criado')
                            ->color('info'),
                        Components\TextEntry::make('updated_at')
                            ->badge()
                            ->label('Data Atualizado')
                            ->color('info'),
                    ]),
                ]),
            ]);
    }


    public static function getRelations(): array
    {
        return [
            ActivitylogRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return auth()->user()->hasRole('Admin')
            ? parent::getEloquentQuery()
            : parent::getEloquentQuery()->whereHas(
                'roles',
                fn(Builder $query) => $query->where('name', '!=', 'Admin')
            );
    }
}
