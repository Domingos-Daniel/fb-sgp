<?php

namespace App\Filament\Resources;

use App\Exports\UsersExport;
use App\Filament\Exports\UserExporter;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Forms;
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
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context): bool => $context === 'create')
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
                    ->required(fn (string $context): bool => $context === 'create')
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
                        fn ($record) => $record->roles->pluck('name')->first() === "Admin" ? "success" : "info",
                        

                    )
                    ->sortable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime(format: "d/m/Y H:i:s")
                    ->sortable()
                    ->label("Data Validado"),
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
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Tables\Actions\Action::make('export')
                // ->label('Exportar')
                // ->action(function () {
                //     return Excel::download(new UsersExporter, 'users.xlsx');
                // }),
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
                       
                    
                    // ExportBulkAction::make('exportcsv')
                    //     ->label('Exportar CSV')
                    //     ->formats([
                    //         ExportFormat::Csv,
                    //     ])
                    //     ->exporter(UserExporter::class) 
                    //     ->columnMapping(true),
 
                    
                    // ExcelExportBulkAction::make('exportxlsx')
                    //     ->label('Exportar Excel')
                    //     ->icon('heroicon-o-document-text')
                    //     ->color('primary'),
                        

                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //ActivitylogRelationManager::class,
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
                fn (Builder $query) => $query->where('name', '!=', 'Admin')
            );
    }
}
