<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use App\Models\EventTemplate;
use App\Models\User;
use App\Services\EventCalculationEngine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Notifications\Notification;
use App\Jobs\GenerateContractJob;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Imprezy';
    protected static ?string $navigationLabel = 'Imprezy';
    protected static ?int $navigationSort = 10;

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Data' => $record->start_date?->format('d.m.Y') ?? '-',
            'Miejsce' => $record->start_place?->name ?? '',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Impreza')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nazwa imprezy')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(1),
                                        Forms\Components\Select::make('event_template_id')
                                            ->label('Szablon')
                                            ->relationship('eventTemplate', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->columnSpan(1)
                                            ->helperText('Szablon bazowy'),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Section::make('Terminy i Uczestnicy')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\DatePicker::make('start_date')
                                            ->label('Start')
                                            ->required()
                                            ->native(false)
                                            ->live()
                                            ->columnSpan(1)
                                            ->afterStateUpdated(function ($state, $set, $get) {
                                                if (!$state) return;
                                                try {
                                                    $duration = (int) ($get('duration_days') ?? 1);
                                                    $start = \Illuminate\Support\Carbon::parse($state);
                                                    $end = $start->copy()->addDays(max(0, $duration - 1))->format('Y-m-d');
                                                    $set('end_date', $end);
                                                } catch (\Throwable $e) {
                                                }
                                            }),

                                        Forms\Components\DatePicker::make('end_date')
                                            ->label('Koniec')
                                            ->native(false)
                                            ->minDate(fn($get) => $get('start_date'))
                                            ->rule('after_or_equal:start_date')
                                            ->live()
                                            ->columnSpan(1)
                                            ->afterStateUpdated(function ($state, $set, $get, \Filament\Forms\Components\Component $component) {
                                                if (!$state) return;
                                                try {
                                                    $start = $get('start_date');
                                                    if (!$start) return;
                                                    $days = \Illuminate\Support\Carbon::parse($start)->diffInDays(\Illuminate\Support\Carbon::parse($state)) + 1;
                                                    $set('duration_days', $days);

                                                    // Trigger cascading update if in Edit Context
                                                    $livewire = $component->getLivewire();
                                                    if (method_exists($livewire, 'handleDurationChange')) {
                                                        $livewire->handleDurationChange();
                                                    }
                                                } catch (\Throwable $_) {
                                                }
                                            }),

                                        Forms\Components\TextInput::make('duration_days')
                                            ->label('Dni')
                                            ->numeric()
                                            ->minValue(1)
                                            ->default(1)
                                            ->live()
                                            ->columnSpan(1)
                                            ->afterStateUpdated(function ($state, $set, $get, \Filament\Forms\Components\Component $component) {
                                                if (!$state) return;
                                                try {
                                                    $start = $get('start_date');
                                                    if (!$start) return;
                                                    $end = \Illuminate\Support\Carbon::parse($start)->addDays(max(0, (int)$state - 1))->format('Y-m-d');
                                                    $set('end_date', $end);

                                                    // Trigger cascading update if in Edit Context
                                                    $livewire = $component->getLivewire();
                                                    if (method_exists($livewire, 'handleDurationChange')) {
                                                        $livewire->handleDurationChange();
                                                    }
                                                } catch (\Throwable $e) {
                                                }
                                            }),

                                        Forms\Components\TextInput::make('participant_count')
                                            ->label('Uczestnicy')
                                            ->numeric()
                                            ->minValue(1)
                                            ->default(1)
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, \Filament\Forms\Components\Component $component) {
                                                Notification::make()
                                                    ->title('Zmiana liczby uczestników')
                                                    ->body('Przeliczam koszty...')
                                                    ->success()
                                                    ->send();

                                                $livewire = $component->getLivewire();
                                                if (method_exists($livewire, 'recalculateCosts')) {
                                                    $livewire->recalculateCosts();
                                                }
                                            })
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('gratis_count')
                                            ->label('Gratis')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (\Filament\Forms\Components\Component $component) => method_exists($component->getLivewire(), 'recalculateCosts') ? $component->getLivewire()->recalculateCosts() : null)
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('staff_count')
                                            ->label('Opiekunowie')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (\Filament\Forms\Components\Component $component) => method_exists($component->getLivewire(), 'recalculateCosts') ? $component->getLivewire()->recalculateCosts() : null)
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('driver_count')
                                            ->label('Kierowcy')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (\Filament\Forms\Components\Component $component) => method_exists($component->getLivewire(), 'recalculateCosts') ? $component->getLivewire()->recalculateCosts() : null)
                                            ->columnSpan(1),

                                        Forms\Components\TextInput::make('guide_count')
                                            ->label('Piloci (liczba)')
                                            ->numeric()
                                            ->minValue(0)
                                            ->default(0)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (\Filament\Forms\Components\Component $component) => method_exists($component->getLivewire(), 'recalculateCosts') ? $component->getLivewire()->recalculateCosts() : null)
                                            ->columnSpan(1),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Section::make('Status i Opiekun')
                            ->schema([
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\Select::make('status')
                                            ->label('Status')
                                            ->options(['draft' => 'Szkic', 'confirmed' => 'Potwierdzona', 'in_progress' => 'W trakcie', 'completed' => 'Zakończona', 'cancelled' => 'Anulowana'])
                                            ->default('draft')
                                            ->required()
                                            ->selectablePlaceholder(false)
                                            ->native(false)
                                            ->columnSpan(1),
                                        Forms\Components\Select::make('assigned_to')
                                            ->label('Opiekun')
                                            ->relationship('assignedUser', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->nullable()
                                            ->columnSpan(1),
                                    ])->columns(2),
                            ]),

                        Forms\Components\Section::make('Dane Klienta')
                            ->schema([
                                Forms\Components\Select::make('contact_id')
                                    ->label('Wybierz klienta')
                                    ->searchable()
                                    ->getSearchResultsUsing(fn(string $search): array => \App\Models\Contact::query()->where('first_name', 'like', "%{$search}%")->orWhere('last_name', 'like', "%{$search}%")->limit(50)->get()->mapWithKeys(fn($contact) => [$contact->id => "{$contact->first_name} {$contact->last_name}"])->toArray())
                                    ->getOptionLabelUsing(fn($value): ?string => ($contact = \App\Models\Contact::find($value)) ? "{$contact->first_name} {$contact->last_name}" : null)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set) {
                                        if ($state) {
                                            $contact = \App\Models\Contact::find($state);
                                            if ($contact) {
                                                $set('client_name', "{$contact->first_name} {$contact->last_name}");
                                                $set('client_email', $contact->email);
                                                $set('client_phone', $contact->phone);
                                            }
                                        }
                                    })
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('first_name')->label('Imię')->required(),
                                        Forms\Components\TextInput::make('last_name')->label('Nazwisko')->required(),
                                        Forms\Components\TextInput::make('email')->label('Email')->email(),
                                        Forms\Components\TextInput::make('phone')->label('Telefon')->tel()
                                    ])
                                    ->createOptionUsing(function (array $data, Forms\Set $set): int {
                                        $contact = \App\Models\Contact::create($data);
                                        $set('client_name', "{$contact->first_name} {$contact->last_name}");
                                        $set('client_email', $contact->email);
                                        $set('client_phone', $contact->phone);
                                        return $contact->id;
                                    }),
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('client_name')->label('Nazwa (dla imprezy)')->required()->columnSpan(1),
                                        Forms\Components\TextInput::make('client_email')->label('Email')->email()->columnSpan(1),
                                        Forms\Components\TextInput::make('client_phone')->label('Telefon')->tel()->columnSpan(1),
                                    ])->columns(3),
                            ]),

                        Forms\Components\Section::make('Uwagi i Notatki')
                            ->schema([
                                Forms\Components\RichEditor::make('notes')->label('Notatki ogólne')->columnSpanFull(),
                                Forms\Components\RichEditor::make('office_notes')->label('Uwagi dla biura')->columnSpanFull(),
                                Forms\Components\RichEditor::make('pilot_notes')->label('Uwagi dla pilota')->columnSpanFull(),
                                Forms\Components\RichEditor::make('driver_notes')->label('Uwagi dla kierowcy')->columnSpanFull(),
                                Forms\Components\RichEditor::make('hotel_notes')->label('Uwagi dla hotelu')->columnSpanFull(),
                            ])
                            ->collapsible()
                            ->collapsed(),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Logistyka')
                            ->schema([
                                Forms\Components\Select::make('bus_id')
                                    ->label('Autokar')
                                    ->relationship('bus', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->live()
                                    ->afterStateUpdated(fn (\Filament\Forms\Components\Component $component) => method_exists($component->getLivewire(), 'recalculateCosts') ? $component->getLivewire()->recalculateCosts() : null),
                                Forms\Components\Select::make('start_place_id')
                                    ->label('Miejsce wyjazdu')
                                    ->relationship('startPlace', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Forms\Set $set, Forms\Get $get) {
                                        if (!$state) {
                                            return;
                                        }

                                        $templateId = $get('event_template_id');
                                        if (!$templateId) {
                                            return;
                                        }

                                        $template = \App\Models\EventTemplate::find($templateId);
                                        if (!$template) {
                                            return;
                                        }

                                        $templateStartId = $template->start_place_id ?? null;
                                        $templateEndId = $template->end_place_id ?? null;

                                        $d1 = 0;
                                        $d2 = 0;

                                        if ($state && $templateStartId) {
                                            if ((int) $state === (int) $templateStartId) {
                                                $d1 = 0;
                                            } else {
                                                $d1 = \App\Models\PlaceDistance::where('from_place_id', $state)
                                                    ->where('to_place_id', $templateStartId)
                                                    ->first()?->distance_km ?? 0;
                                            }
                                        }

                                        if ($templateEndId && $state) {
                                            if ((int) $templateEndId === (int) $state) {
                                                $d2 = 0;
                                            } else {
                                                $d2 = \App\Models\PlaceDistance::where('from_place_id', $templateEndId)
                                                    ->where('to_place_id', $state)
                                                    ->first()?->distance_km ?? 0;
                                            }
                                        }

                                        $set('transfer_km', $d1 + $d2);
                                        
                                        $livewire = $get('this'); // 'this' might not be available in Get helper directly in closure signature?
                                        // Use component context
                                    }, function(\Filament\Forms\Components\Component $component) {
                                         if(method_exists($component->getLivewire(), 'recalculateCosts')) {
                                             $component->getLivewire()->recalculateCosts();
                                         }
                                    }),
                                Forms\Components\Select::make('markup_id')
                                    ->label('Narzut')
                                    ->relationship('markup', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->live()
                                    ->afterStateUpdated(fn (\Filament\Forms\Components\Component $component) => method_exists($component->getLivewire(), 'recalculateCosts') ? $component->getLivewire()->recalculateCosts() : null),
                                Forms\Components\Grid::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('transfer_km')
                                            ->label('Transfer (km)')
                                            ->numeric()
                                            ->default(0)
                                            ->helperText('Podpowiadane z tabeli odległości; możesz edytować ręcznie.')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (\Filament\Forms\Components\Component $component) => method_exists($component->getLivewire(), 'recalculateCosts') ? $component->getLivewire()->recalculateCosts() : null)
                                            ->columnSpan(1),
                                        Forms\Components\TextInput::make('program_km')
                                            ->label('Program (km)')
                                            ->numeric()
                                            ->default(0)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (\Filament\Forms\Components\Component $component) => method_exists($component->getLivewire(), 'recalculateCosts') ? $component->getLivewire()->recalculateCosts() : null)
                                            ->columnSpan(1),
                                        Forms\Components\TextInput::make('transport_price_override')
                                            ->label('Koszt transportu (Ręczny)')
                                            ->numeric()
                                            ->prefix('PLN')  
                                            ->minValue(0)
                                            ->placeholder('Automatycznie')
                                            ->helperText('Wpisanie kwoty nadpisuje automatyczną kalkulację wg stawek autokaru.')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (\Filament\Forms\Components\Component $component) => method_exists($component->getLivewire(), 'recalculateCosts') ? $component->getLivewire()->recalculateCosts() : null)
                                            ->columnSpanFull(),
                                    ])->columns(2),
                                Forms\Components\TextInput::make('pickup_place')->label('Miejsce podstawienia')->maxLength(255),
                                Forms\Components\DateTimePicker::make('pickup_datetime')->label('Data i godzina podstawienia')->native(false),
                            ]),

                        Forms\Components\Section::make('Piloci')
                            ->collapsible()
                            ->schema([
                                Forms\Components\CheckboxList::make('guides')
                                    ->label('')
                                    ->relationship('guides', 'name', fn($query) => $query->whereHas('roles', fn($q) => $q->where('name', 'pilot')))
                                    ->columns(2)
                                    ->bulkToggleable()
                                    ->searchable(),
                            ]),

                        Forms\Components\Section::make('Dostęp do Portalu')
                            ->collapsible()
                            ->collapsed()
                            ->schema([
                                Forms\Components\TextInput::make('public_code')->label('Kod Publiczny')->disabled(),
                                Forms\Components\TextInput::make('access_code_manager')->label('Kod Kierownika')->default(fn() => strtoupper(\Illuminate\Support\Str::random(8))),
                                Forms\Components\TextInput::make('access_code_participant')->label('Kod Uczestnika')->default(fn() => strtoupper(\Illuminate\Support\Str::random(8))),
                            ]),

                        // Global Custom Prices Section Removed


                        // Custom prices handled in Per-Day view (EditEvent page)

                        Forms\Components\Section::make('Finanse (Podgląd)')
                            ->schema([
                                Forms\Components\TextInput::make('total_cost')
                                    ->label('Koszt całkowity')
                                    ->disabled()
                                    ->formatStateUsing(fn($state) => number_format((float)$state, 2, '.', ' ') . ' PLN')
                                    ->helperText(function ($record) {
                                        if (! $record) {
                                            return null;
                                        }

                                        $calc = (new EventCalculationEngine())->calculate($record);
                                        $currencies = $calc['currencies'] ?? [];
                                        if (empty($currencies)) {
                                            return null;
                                        }

                                        $parts = [];
                                        foreach ($currencies as $code => $amount) {
                                            $parts[] = number_format((float) $amount, 2, '.', ' ') . ' ' . $code;
                                        }

                                        return '+ ' . implode(' + ', $parts);
                                    }),
                                Forms\Components\TextInput::make('calculated_price_per_person')
                                    ->label('Cena/osobę')
                                    ->disabled()
                                    ->formatStateUsing(fn($state) => number_format((float)$state, 2, '.', ' ') . ' PLN')
                                    ->helperText(function ($record) {
                                        if (! $record) {
                                            return null;
                                        }

                                        $calc = (new EventCalculationEngine())->calculate($record);
                                        $currencies = $calc['currencies_per_person'] ?? [];
                                        if (empty($currencies)) {
                                            return null;
                                        }

                                        $parts = [];
                                        foreach ($currencies as $code => $amount) {
                                            $parts[] = number_format((float) $amount, 2, '.', ' ') . ' ' . $code;
                                        }

                                        return '+ ' . implode(' + ', $parts);
                                    }),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nazwa imprezy')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('duration_days')
                    ->label('Dni')
                    ->numeric()
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('eventTemplate.name')
                    ->label('Szablon')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('client_name')
                    ->label('Klient')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Data rozpoczęcia')
                    ->date('d.m.Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Data zakończenia')
                    ->date('d.m.Y')
                    ->sortable()
                    ->placeholder('Jednodniowa'),

                Tables\Columns\TextColumn::make('participant_count')
                    ->label('Uczestnicy')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Koszt')
                    ->money('PLN')
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('calculated_price_per_person')
                    ->label('Cena/os')
                    ->money('PLN')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('public_code')
                    ->label('Kod')
                    ->copyable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'secondary' => 'draft',
                        'success' => 'confirmed',
                        'warning' => 'in_progress',
                        'primary' => 'completed',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Szkic',
                        'confirmed' => 'Potwierdzona',
                        'in_progress' => 'W trakcie',
                        'completed' => 'Zakończona',
                        'cancelled' => 'Anulowana',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('assignedUser.name')
                    ->label('Przypisany do')
                    ->placeholder('Nie przypisano')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Utworzona')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Szkic',
                        'confirmed' => 'Potwierdzona',
                        'in_progress' => 'W trakcie',
                        'completed' => 'Zakończona',
                        'cancelled' => 'Anulowana',
                    ]),

                Tables\Filters\SelectFilter::make('event_template_id')
                    ->label('Szablon')
                    ->relationship('eventTemplate', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('start_date')
                    ->label('Data rozpoczęcia')
                    ->form([
                        Forms\Components\DatePicker::make('from')
                            ->label('Od'),
                        Forms\Components\DatePicker::make('until')
                            ->label('Do'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('start_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('tasks')
                    ->label('Zadania')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('warning')
                    ->modalContent(fn(Event $record) => view('livewire.task-manager-wrapper', ['taskable' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false),
                // action 'program' removed to hide the "Program" link from UI
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn(Event $record) => $record->status === 'draft'),

                Tables\Actions\Action::make('change_status')
                    ->label('Zmień status')
                    ->icon('heroicon-o-arrow-path')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Nowy status')
                            ->options([
                                'draft' => 'Szkic',
                                'confirmed' => 'Potwierdzona',
                                'in_progress' => 'W trakcie',
                                'completed' => 'Zakończona',
                                'cancelled' => 'Anulowana',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('reason')
                            ->label('Powód zmiany')
                            ->rows(3),
                    ])
                    ->action(function (Event $record, array $data) {
                        $record->changeStatus($data['status'], $data['reason'] ?? null);
                    }),
                Tables\Actions\Action::make('generate_contract')
                    ->label('Generuj umowę')
                    ->icon('heroicon-o-document-text')
                    ->form([
                        Forms\Components\Select::make('contract_template_id')
                            ->label('Szablon umowy')
                            ->options(\App\Models\ContractTemplate::all()->pluck('name', 'id'))
                            ->required(),
                        Forms\Components\TextInput::make('contract_number')
                            ->label('Numer umowy')
                            ->default(fn() => 'UM/' . date('Y') . '/' . rand(100, 999))
                            ->required(),
                        Forms\Components\DatePicker::make('date_issued')
                            ->label('Data wystawienia')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('place_issued')
                            ->label('Miejsce wystawienia')
                            ->default('Warszawa'),
                    ])
                    ->action(function (Event $record, array $data) {
                        // Dispatch contract generation to queue to avoid blocking UI
                        GenerateContractJob::dispatch(
                            $record->id,
                            (int) $data['contract_template_id'],
                            (string) $data['contract_number'],
                            $data['date_issued'],
                            (string) $data['place_issued']
                        );

                        Notification::make()
                            ->title('Generowanie umowy')
                            ->success()
                            ->body('Zadanie generowania umowy zostało wysłane do kolejki. Po ukończeniu znajdziesz umowę w sekcji Umowy.')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProgramPointsRelationManager::class,
            RelationManagers\ReservationsRelationManager::class,
            RelationGroup::make('Finanse', [
                RelationManagers\OffersRelationManager::class, // Oferty
                RelationManagers\ContractsRelationManager::class, // Umowy
                RelationManagers\CostsRelationManager::class, // Koszty
                RelationManagers\PaymentsRelationManager::class, // Wpłaty
                RelationManagers\CancellationsRelationManager::class, // Rezygnacje
            ]),
            RelationGroup::make('Dokumenty', [
                RelationManagers\DocumentsRelationManager::class,
            ]),
            RelationGroup::make('Uczestnicy', [
                RelationManagers\UsersRelationManager::class,
            ]),
            RelationManagers\PilotSupportRelationManager::class,
            RelationGroup::make('Organizacja', [
                RelationManagers\ChecklistRelationManager::class,
                \App\Filament\Resources\TaskResource\RelationManagers\TasksRelationManager::class,
                \App\Filament\RelationManagers\EmailsRelationManager::class,
            ]),
            RelationGroup::make('Historia', [
                RelationManagers\HistoryRelationManager::class,
                RelationManagers\SnapshotsRelationManager::class,
            ]),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['eventTemplate', 'assignedUser']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
            'edit-program' => Pages\EditEventProgram::route('/{record}/program'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'in_progress')->count();
    }
}
