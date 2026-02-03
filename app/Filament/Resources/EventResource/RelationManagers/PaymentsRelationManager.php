<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Models\EventCost;
use App\Models\EventPaymentCostAllocation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = 'Wpłaty od klienta';
    protected static ?string $icon = 'heroicon-o-banknotes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Szczegóły wpłaty')
                    ->schema([
                        Forms\Components\TextInput::make('description')
                            ->label('Opis')
                            ->placeholder('np. I rata, Dopłata')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('amount')
                            ->label('Kwota')
                            ->numeric()
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\Select::make('currency')
                            ->label('Waluta')
                            ->options([
                                'PLN' => 'PLN',
                                'EUR' => 'EUR',
                                'USD' => 'USD',
                                'CZK' => 'CZK',
                                'HUF' => 'HUF',
                                'GBP' => 'GBP',
                            ])
                            ->default('PLN')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Data płatności')
                            ->default(now())
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\Select::make('payment_type_id')
                            ->label('Forma płatności')
                            ->relationship('paymentType', 'name')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required()->label('Nazwa'),
                            ])
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('is_advance')
                            ->label('Zaliczka')
                            ->inline(false)
                            ->columnSpan(1),
                        Forms\Components\Hidden::make('source')
                            ->default('office'),
                        Forms\Components\Hidden::make('created_by_user_id')
                            ->default(fn () => Auth::id()),
                    ])->columns(2)->compact(),
                
                Forms\Components\Section::make('Dokumentacja')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Numer faktury')
                            ->columnSpan(2),
                        Forms\Components\FileUpload::make('documents')
                            ->label('Załączniki')
                            ->multiple()
                            ->directory('event-payments-documents')
                            ->downloadable()
                            ->openable()
                            ->maxFiles(10)
                            ->columnSpanFull(),
                    ])->columns(2)->collapsed()->compact(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->paginationPageOptions([15])
            ->defaultPaginationPageOption(15)
            ->recordTitleAttribute('description')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['paymentType']))
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Data')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Opis')
                    ->searchable()
                    ->description(fn ($record) => $record->paymentType?->name),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Kwota')
                    ->formatStateUsing(fn ($state, $record) => number_format((float) $state, 2, ',', ' ') . ' ' . ($record->currency ?? 'PLN'))
                    ->alignEnd()
                    ->sortable(),
                Tables\Columns\TextColumn::make('currency')
                    ->label('Waluta')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('source')
                    ->label('Źródło')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'pilot_cash' => 'Pilot (gotówka)',
                        'online' => 'Online',
                        default => 'Biuro',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'pilot_cash' => 'info',
                        'online' => 'success',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('createdBy.name')
                    ->label('Dodane przez')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('is_advance')
                    ->label('Zaliczka')
                    ->boolean()
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Faktura')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_advance')
                    ->label('Typ')
                    ->placeholder('Wszystkie')
                    ->trueLabel('Tylko zaliczki')
                    ->falseLabel('Bez zaliczek'),
                Tables\Filters\SelectFilter::make('source')
                    ->label('Źródło')
                    ->options([
                        'office' => 'Biuro',
                        'online' => 'Online',
                        'pilot_cash' => 'Pilot (gotówka)',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Dodaj wpłatę')
                    ->icon('heroicon-o-plus')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['source'] = $data['source'] ?? 'office';
                        $data['currency'] = $data['currency'] ?? 'PLN';
                        $data['created_by_user_id'] = $data['created_by_user_id'] ?? Auth::id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hiddenLabel(),
                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}

