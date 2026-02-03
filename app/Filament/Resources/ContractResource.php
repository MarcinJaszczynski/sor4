<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContractResource\Pages;
use App\Filament\Resources\ContractResource\RelationManagers;
use App\Models\Contract;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getGloballySearchableAttributes(): array
    {
        return ['contract_number', 'client.name', 'client.email'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Klient' => $record->client->name ?? '-',
            'Numer' => $record->contract_number,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Szczegóły umowy')
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->relationship('event', 'name')
                            ->required()
                            ->label('Impreza')
                            ->disabled() // Usually created from Event context
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('contract_number')
                            ->label('Numer umowy')
                            ->columnSpan(2),
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Szkic',
                                'generated' => 'Wygenerowana',
                                'signed' => 'Podpisana',
                                'cancelled' => 'Anulowana',
                            ])
                            ->required()
                            ->columnSpan(2),
                        Forms\Components\DatePicker::make('date_issued')
                            ->label('Data wystawienia')
                            ->columnSpan(3),
                        Forms\Components\TextInput::make('place_issued')
                            ->label('Miejsce wystawienia')
                            ->columnSpan(3),
                    ])->columns(6)->compact(),

                Forms\Components\Section::make('Finanse')
                    ->schema([
                        Forms\Components\TextInput::make('locked_price_per_person')
                            ->label('Zablokowana cena za osobę')
                            ->helperText('Kwota do zapłaty przez klienta (zablokowana w momencie generowania)')
                            ->numeric()
                            ->suffix('PLN')
                            ->default(function ($get) {
                                // Attempt to fetch from Event if possible
                                if ($eventId = $get('event_id')) {
                                    $event = \App\Models\Event::find($eventId);
                                    if ($event) {
                                        // Try event scoped prices first
                                        $price = $event->pricePerPerson->first()?->price_with_tax;
                                        if ($price) return $price;
                                        
                                        // Fallback to template prices
                                        return $event->eventTemplate?->eventTemplateQtys->first()?->eventTemplatePricePerPeople->first()?->price_with_tax ?? 0;
                                    }
                                }
                                return 0;
                            }),
                        Forms\Components\TextInput::make('total_amount')
                             ->label('Całkowita kwota umowy')
                             ->numeric()
                             ->suffix('PLN'),
                    ])->columns(2),
                
                Forms\Components\Section::make('Treść umowy')
                    ->schema([
                        Forms\Components\RichEditor::make('content')
                            ->label('Treść')
                            ->columnSpanFull()
                            ->required(),
                    ]),

                Forms\Components\Section::make('Automatyczne raty')
                    ->visible(fn ($livewire) => $livewire instanceof \App\Filament\Resources\ContractResource\Pages\CreateContract)
                    ->schema([
                        Forms\Components\Toggle::make('auto_generate_installments')
                            ->label('Utwórz harmonogram rat po zapisie')
                            ->default(true)
                            ->inline(false),
                        Forms\Components\Toggle::make('replace_existing_installments')
                            ->label('Usuń istniejące raty i utwórz od nowa')
                            ->default(false)
                            ->inline(false)
                            ->visible(fn (Forms\Get $get) => (bool) $get('auto_generate_installments')),
                        Forms\Components\TextInput::make('installment_deposit_percent')
                            ->label('Zaliczka (%)')
                            ->numeric()
                            ->default(30)
                            ->required()
                            ->visible(fn (Forms\Get $get) => (bool) $get('auto_generate_installments')),
                        Forms\Components\DatePicker::make('installment_deposit_due_date')
                            ->label('Termin zaliczki')
                            ->default(now())
                            ->required()
                            ->visible(fn (Forms\Get $get) => (bool) $get('auto_generate_installments')),
                        Forms\Components\TextInput::make('installment_final_due_days_before_start')
                            ->label('Ile dni przed startem termin dopłaty')
                            ->numeric()
                            ->default(14)
                            ->required()
                            ->visible(fn (Forms\Get $get) => (bool) $get('auto_generate_installments')),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('contract_number')
                    ->label('Numer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('event.name')
                    ->label('Impreza')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'generated' => 'info',
                        'signed' => 'success',
                        'cancelled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('date_issued')
                    ->date()
                    ->label('Data'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('print')
                    ->label('Drukuj')
                    ->icon('heroicon-o-printer')
                    ->url(fn (Contract $record) => route('client.contract', $record->uuid))
                    ->openUrlInNewTab(),
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
            \App\Filament\Resources\TaskResource\RelationManagers\TasksRelationManager::class,
            RelationManagers\AddendumsRelationManager::class,
            RelationManagers\ParticipantsRelationManager::class,
            RelationManagers\InstallmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContract::route('/create'),
            'edit' => Pages\EditContract::route('/{record}/edit'),
        ];
    }
}
