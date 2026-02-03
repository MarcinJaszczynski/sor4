<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailAccountResource\Pages;
use App\Filament\Resources\EmailAccountResource\RelationManagers;
use App\Models\EmailAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmailAccountResource extends Resource
{
    protected static ?string $model = EmailAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-at-symbol';
    protected static ?string $navigationGroup = 'Poczta';
    protected static ?int $navigationSort = 100;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Podstawowe informacje')
                    ->schema([
                        Forms\Components\TextInput::make('account_name')
                            ->label('Nazwa konta')
                            ->required()
                            ->placeholder('np. Firmowy Gmail, Prywatna skrzynka')
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('email')
                            ->label('Adres E-mail')
                            ->email()
                            ->required()
                            ->columnSpan(2),
                        Forms\Components\Select::make('user_id')
                            ->label('Właściciel')
                            ->relationship('user', 'name')
                            ->default(auth()->id())
                            ->required()
                            ->columnSpan(2),
                        
                        Forms\Components\TextInput::make('from_name')
                            ->label('Nazwa nadawcy')
                            ->columnSpan(2),
                        Forms\Components\Select::make('visibility')
                            ->label('Widoczność')
                            ->options([
                                'private' => 'Prywatne',
                                'public' => 'Publiczne',
                                'shared' => 'Udostępnione',
                            ])
                            ->required()
                            ->default('private')
                            ->live()
                            ->columnSpan(2),
                        Forms\Components\Select::make('sharedUsers')
                            ->label('Udostępnij użytkownikom')
                            ->multiple()
                            ->relationship('sharedUsers', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get) => $get('visibility') === 'shared')
                            ->columnSpan(2),
                    ])->columns(6)->compact(),

                Forms\Components\Section::make('Serwer Przychodzący (IMAP)')
                    ->schema([
                        Forms\Components\TextInput::make('imap_host')
                            ->label('Host IMAP')
                            ->placeholder('imap.gmail.com')
                            ->required()
                            ->default('imap.gmail.com')
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('imap_port')
                            ->label('Port')
                            ->numeric()
                            ->default(993)
                            ->columnSpan(1),
                        Forms\Components\Select::make('imap_encryption')
                            ->label('Szyfrowanie')
                            ->options([
                                'ssl' => 'SSL',
                                'tls' => 'TLS',
                                'none' => 'Brak',
                            ])
                            ->default('ssl')
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('username')
                            ->label('Login')
                            ->placeholder('Zazwyczaj e-mail')
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('password')
                            ->label('Hasło')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $context): bool => $context === 'create')
                            ->columnSpan(4),
                    ])->columns(6)->compact(),

                Forms\Components\Section::make('Serwer Wychodzący (SMTP)')
                    ->schema([
                        Forms\Components\TextInput::make('smtp_host')
                            ->label('Host SMTP')
                            ->placeholder('smtp.gmail.com')
                            ->required()
                            ->default('smtp.gmail.com')
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('smtp_port')
                            ->label('Port')
                            ->numeric()
                            ->default(587)
                            ->columnSpan(1),
                        Forms\Components\Select::make('smtp_encryption')
                            ->label('Szyfrowanie')
                            ->options([
                                'ssl' => 'SSL',
                                'tls' => 'TLS',
                                'none' => 'Brak',
                            ])
                            ->default('tls')
                            ->columnSpan(1),
                    ])->columns(4)->compact(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('account_name')
                    ->label('Nazwa konta')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),
                Tables\Columns\IconColumn::make('visibility')
                    ->label('Dostęp')
                    ->icon(fn (string $state): string => match ($state) {
                        'private' => 'heroicon-o-lock-closed',
                        'public' => 'heroicon-o-users',
                        'shared' => 'heroicon-o-share',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'private' => 'danger',
                        'public' => 'success',
                        'shared' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Właściciel')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->forUser(auth()->id());
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailAccounts::route('/'),
            'create' => Pages\CreateEmailAccount::route('/create'),
            'edit' => Pages\EditEmailAccount::route('/{record}/edit'),
        ];
    }
}
