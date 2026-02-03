<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusResource\Pages;
use App\Models\Bus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BusResource extends Resource
{    protected static ?string $model = Bus::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'Transport';
    protected static ?string $navigationLabel = 'Autokary';
    protected static ?int $navigationSort = 95;
    protected static ?string $modelLabel = 'autokar';
    protected static ?string $pluralModelLabel = 'autokary';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dane autokaru')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nazwa')
                        ->required()
                        ->columnSpan(3),
                    Forms\Components\TextInput::make('capacity')
                        ->label('Pojemność')
                        ->numeric()
                        ->default(55)
                        ->required()
                        ->columnSpan(1),
                    Forms\Components\Select::make('contractor_id')
                        ->label('Kontrahent')
                        ->relationship('contractor', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->columnSpan(2),
                    Forms\Components\Toggle::make('is_real')
                        ->label('Rzeczywisty autokar (należy do kontrahenta)')
                        ->default(false)
                        ->columnSpan(1),
                    Forms\Components\TextInput::make('currency')
                        ->label('Waluta')
                        ->default('PLN')
                        ->required()
                        ->columnSpan(1),
                    Forms\Components\Toggle::make('convert_to_pln')
                        ->label('Przeliczaj na PLN')
                        ->default(true)
                        ->inline(false)
                        ->columnSpan(1),

                    Forms\Components\TextInput::make('package_price_per_day')
                        ->label('Cena pakiet/dzień')
                        ->numeric()
                        ->required()
                        ->columnSpan(2),
                    Forms\Components\TextInput::make('package_km_per_day')
                        ->label('Limit km/dzień')
                        ->numeric()
                        ->default(300)
                        ->required()
                        ->columnSpan(2),
                    Forms\Components\TextInput::make('extra_km_price')
                        ->label('Cena nadbagaż (km)')
                        ->numeric()
                        ->required()
                        ->columnSpan(2),

                    Forms\Components\Textarea::make('description')
                        ->label('Opis')
                        ->nullable()
                        ->columnSpanFull(),
                ])->columns(6)->compact(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('name')->label('Nazwa')->sortable(),
            Tables\Columns\TextColumn::make('description')->label('Opis')->limit(40),
            Tables\Columns\TextColumn::make('capacity')->label('Pojemność')->sortable(),
            Tables\Columns\TextColumn::make('package_price_per_day')->label('Cena za pakiet na dzień')->sortable(),
            Tables\Columns\TextColumn::make('package_km_per_day')->label('Km w pakiecie')->sortable(),
            Tables\Columns\TextColumn::make('extra_km_price')->label('Cena za km poza pakietem')->sortable(),
            Tables\Columns\TextColumn::make('contractor.name')->label('Kontrahent')->toggleable(isToggledHiddenByDefault: true),
            Tables\Columns\IconColumn::make('is_real')->label('Rzeczywisty')->boolean(),
            Tables\Columns\TextColumn::make('currency')
                ->label('Waluta')
                ->sortable(),
            Tables\Columns\IconColumn::make('convert_to_pln')
                ->label('Przeliczaj na złotówki')
                ->boolean(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBuses::route('/'),
            'create' => Pages\CreateBus::route('/create'),
            'edit' => Pages\EditBus::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = \App\Models\User::query()->find(\Illuminate\Support\Facades\Auth::id());
        if ($user && $user->roles && $user->roles->contains('name', 'admin')) {
            return true;
        }
        if ($user && $user->roles && $user->roles->flatMap->permissions->contains('name', 'view bus')) {
            return true;
        }
        return false;
    }
    public static function canView(
        $record
    ): bool {
        $user = \App\Models\User::query()->find(\Illuminate\Support\Facades\Auth::id());
        if ($user && $user->roles && $user->roles->contains('name', 'admin')) {
            return true;
        }
        if ($user && $user->roles && $user->roles->flatMap->permissions->contains('name', 'view bus')) {
            return true;
        }
        return false;
    }
    public static function canCreate(): bool
    {
        $user = \App\Models\User::query()->find(\Illuminate\Support\Facades\Auth::id());
        if ($user && $user->roles && $user->roles->contains('name', 'admin')) {
            return true;
        }
        if ($user && $user->roles && $user->roles->flatMap->permissions->contains('name', 'create bus')) {
            return true;
        }
        return false;
    }
    public static function canEdit($record): bool
    {
        $user = \App\Models\User::query()->find(\Illuminate\Support\Facades\Auth::id());
        if ($user && $user->roles && $user->roles->contains('name', 'admin')) {
            return true;
        }
        if ($user && $user->roles && $user->roles->flatMap->permissions->contains('name', 'update bus')) {
            return true;
        }
        return false;
    }
    public static function canDelete($record): bool
    {
        $user = \App\Models\User::query()->find(\Illuminate\Support\Facades\Auth::id());
        if ($user && $user->roles && $user->roles->contains('name', 'admin')) {
            return true;
        }
        if ($user && $user->roles && $user->roles->flatMap->permissions->contains('name', 'delete bus')) {
            return true;
        }
        return false;
    }
}
