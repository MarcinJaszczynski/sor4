<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Models\EventPayment;
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

class CostsRelationManager extends RelationManager
{
    protected static string $relationship = 'costs';
    protected static ?string $title = 'Koszty i rozliczenia';
    protected static ?string $icon = 'heroicon-o-calculator';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Podstawowe informacje')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Opis kosztu')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),
                        Forms\Components\Select::make('contractor_id')
                            ->label('Kontrahent')
                            ->relationship('contractor', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required()->label('Nazwa'),
                            ])
                            ->columnSpan(1),
                        Forms\Components\Select::make('category')
                            ->label('Kategoria kosztu (Budżet)')
                            ->options([
                                'accommodation' => 'Noclegi i wyżywienie',
                                'transport' => 'Transport',
                                'insurance' => 'Ubezpieczenie',
                                'program' => 'Program (wybierz punkt)',
                                'other' => 'Inne',
                            ])
                            ->default('other')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state !== 'program') {
                                    $set('event_program_point_id', null);
                                }
                            })
                            ->columnSpan(1),
                        Forms\Components\Select::make('event_program_point_id')
                            ->label('Dotyczy punktu programu')
                            ->relationship('programPoint', 'name', function (Builder $query, RelationManager $livewire) {
                                // Filter points for this event
                                $event = $livewire->getOwnerRecord();
                                return $query->where('event_id', $event->id);
                            })
                            ->searchable()
                            ->preload()
                            ->visible(fn (Forms\Get $get) => $get('category') === 'program')
                            ->columnSpan(1),
                    ])->columns(2)->compact(),
                
                Forms\Components\Section::make('Kwota i płatność')
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Kwota planowana')
                            ->helperText('Planowana kwota (kwota faktury / zobowiązania)')
                            ->numeric()
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\TextInput::make('paid_amount')
                            ->label('Kwota zapłacona')
                            ->helperText('Ile faktycznie przelano')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1),
                        Forms\Components\Select::make('currency_id')
                            ->label('Waluta')
                            ->relationship('currency', 'symbol')
                            ->default(function () {
                                return \App\Models\Currency::where('symbol', 'PLN')->first()?->id;
                            })
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('payment_date')
                            ->label('Data płatności')
                            ->columnSpan(1),
                        Forms\Components\Select::make('payer_id')
                            ->label('Płatnik')
                            ->relationship('payer', 'name')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required()->label('Nazwa'),
                            ])
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\Select::make('payment_type_id')
                            ->label('Forma płatności')
                            ->relationship('paymentType', 'name')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required()->label('Nazwa'),
                            ])
                            ->columnSpan(1),
                        Forms\Components\Toggle::make('is_paid')
                            ->label('Opłacone')
                            ->default(false)
                            ->inline(false)
                            ->columnSpan(1),
                    ])->columns(2)->compact(),
                
                Forms\Components\Section::make('Dokumentacja')
                    ->schema([
                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Numer faktury')
                            ->columnSpan(2),
                        Forms\Components\FileUpload::make('documents')
                            ->label('Załączniki')
                            ->multiple()
                            ->directory('event-costs-documents')
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
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['currency', 'contractor', 'payer', 'paymentType']))
            ->columns([
                Tables\Columns\TextColumn::make('payment_date')
                    ->label('Data')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Opis')
                    ->searchable()
                    ->description(fn ($record) => $record->contractor?->name),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Kwota planowana')
                    ->formatStateUsing(fn ($record) => 
                        number_format($record->amount, 2, ',', ' ') . ' ' . ($record->currency?->symbol ?? 'PLN')
                    )
                    ->color('gray')
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_amount')
                    ->label('Zapłacono')
                    ->formatStateUsing(fn ($record) => 
                        number_format($record->paid_amount, 2, ',', ' ') . ' ' . ($record->currency?->symbol ?? 'PLN')
                    )
                    ->color(fn ($record) => $record->paid_amount >= $record->amount ? 'success' : ($record->paid_amount > 0 ? 'warning' : 'danger'))
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_paid')

                    ->label('✓')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('payer.name')
                    ->label('Płatnik')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('paymentType.name')
                    ->label('Forma')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Faktura')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_paid')
                    ->label('Status')
                    ->placeholder('Wszystkie')
                    ->trueLabel('Opłacone')
                    ->falseLabel('Nieopłacone'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Dodaj koszt')
                    ->icon('heroicon-o-plus'),
            ])
            ->actions([
                Tables\Actions\Action::make('tasks')
                    ->label('Zadania')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('warning')
                    ->modalContent(fn ($record) => view('livewire.task-manager-wrapper', ['taskable' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->hiddenLabel(),
                Tables\Actions\EditAction::make()
                    ->hiddenLabel(),
                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('payment_date', 'desc');
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\EventResource\Widgets\EventCostsStats::class,
            \App\Filament\Resources\EventResource\Widgets\EventBudgetBreakdown::class,
        ];
    }
}

