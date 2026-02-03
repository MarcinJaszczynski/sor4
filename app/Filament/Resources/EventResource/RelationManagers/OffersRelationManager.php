<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class OffersRelationManager extends RelationManager
{
    protected static string $relationship = 'offers';
    protected static ?string $title = 'Oferty';
    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Szczegóły oferty')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nazwa oferty')
                            ->default(fn () => 'Oferta #' . (rand(1000, 9999)))
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Select::make('offer_template_id')
                            ->label('Użyj szablonu')
                            ->relationship('template', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => \App\Models\OfferTemplate::query()->where('is_default', true)->value('id'))
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\RichEditor::make('introduction'),
                                Forms\Components\RichEditor::make('terms'),
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if (!$state) return;
                                $template = \App\Models\OfferTemplate::find($state);
                                if ($template) {
                                    $set('introduction', $template->introduction);
                                    $set('terms', $template->terms);
                                }
                            }),

                        Forms\Components\TextInput::make('participant_count')
                            ->label('Liczba uczestników')
                            ->numeric()
                            ->default(fn (\App\Filament\Resources\EventResource\RelationManagers\OffersRelationManager $livewire) => $livewire->getOwnerRecord()->participant_count)
                            ->required(),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Szkic',
                                'sent' => 'Wysłana',
                                'accepted' => 'Zaakceptowana',
                                'rejected' => 'Odrzucona',
                            ])
                            ->default('draft')
                            ->required(),

                        Forms\Components\DatePicker::make('valid_until')
                            ->label('Ważna do')
                            ->default(now()->addDays(14)),
                    ])->columns(2),

                Forms\Components\Tabs::make('Treść')
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Wstęp')
                            ->schema([
                                Forms\Components\RichEditor::make('introduction')
                                    ->label('Wstęp / Powitanie'),
                            ]),
                        Forms\Components\Tabs\Tab::make('Podsumowanie')
                            ->schema([
                                Forms\Components\RichEditor::make('summary')
                                    ->label('Podsumowanie'),
                            ]),
                        Forms\Components\Tabs\Tab::make('Warunki')
                            ->schema([
                                Forms\Components\RichEditor::make('terms')
                                    ->label('Warunki / Regulamin'),
                            ]),
                    ]),
                
                Forms\Components\Section::make('Konfiguracja punktów programu')
                    ->description('Dostosuj punkty programu dla tej oferty (opcjonalność, ceny)')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->label('Lista punktów')
                            ->schema([
                                Forms\Components\Select::make('event_program_point_id')
                                    ->label('Punkt programu')
                                    ->relationship(
                                        'programPoint', 
                                        'name', 
                                        fn (Builder $query, \App\Filament\Resources\EventResource\RelationManagers\OffersRelationManager $livewire) => 
                                            $query->where('event_id', $livewire->getOwnerRecord()->id)
                                    )
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(4),
                                
                                Forms\Components\Toggle::make('is_optional')
                                    ->label('Opcjonalny')
                                    ->helperText('Klient może zrezygnować')
                                    ->inline(false),
                                
                                Forms\Components\Toggle::make('is_included')
                                    ->label('W cenie')
                                    ->helperText('Wliczone w cenę oferty')
                                    ->inline(false),
                                
                                Forms\Components\TextInput::make('custom_price')
                                    ->label('Cena specjalna')
                                    ->numeric()
                                    ->placeHolder('Domyślna')
                                    ->columnSpan(2),
                            ])
                            ->columns(2)
                            ->addable(false)
                            ->deletable(false)
                            ->itemLabel(fn ($state) => \App\Models\EventProgramPoint::find($state['event_program_point_id'])?->name ?? 'Punkt')
                    ])
                    ->hidden(fn ($operation) => $operation === 'create'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nazwa')
                    ->searchable()
                    ->description(fn ($record) => 'Utworzono: ' . $record->created_at->format('d.m.Y H:i')),
                Tables\Columns\TextColumn::make('participant_count')
                    ->label('Osób')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Cena całkowita')
                    ->money('PLN')
                    ->alignEnd()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Ważna do')
                    ->date('d.m.Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nowa oferta')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = Auth::id();

                        if (empty($data['offer_template_id'])) {
                            $data['offer_template_id'] = \App\Models\OfferTemplate::query()->where('is_default', true)->value('id');
                        }

                        if (! empty($data['offer_template_id'])) {
                            $template = \App\Models\OfferTemplate::query()->find($data['offer_template_id']);
                            if ($template) {
                                if (empty($data['introduction'])) {
                                    $data['introduction'] = $template->introduction;
                                }
                                if (empty($data['terms'])) {
                                    $data['terms'] = $template->terms;
                                }
                            }
                        }

                        return $data;
                    })
                    ->after(function ($record) {
                        // Kopiuj punkty programu imprezy do items oferty
                        $event = $record->event;
                        if (!$event) return;

                        $programPoints = $event->programPoints;
                        foreach ($programPoints as $point) {
                            \App\Models\OfferItem::create([
                                'offer_id' => $record->id,
                                'event_program_point_id' => $point->id,
                                'is_optional' => false, // Domyślnie wymagane, można zmienić
                                'is_included' => true,  // Domyślnie w cenie
                                'quantity' => $point->quantity ?? 1,
                                'custom_price' => null, // null = cena z punktu
                            ]);
                        }
                        
                        // Jeśli wybrano szablon, a pola są puste, wypełnij je (jeśli create nie zadziałało przez live)
                        // Ale logika live() powinna zadziałać w formularzu
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hiddenLabel(),
                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel(),
                Tables\Actions\Action::make('print')
                    ->icon('heroicon-o-printer')
                    ->hiddenLabel()
                    ->tooltip('Drukuj (PDF)')
                    ->url(fn ($record) => route('admin.offer.pdf', $record->id))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('word')
                    ->icon('heroicon-o-document-text')
                    ->hiddenLabel()
                    ->tooltip('Eksportuj do Word')
                    ->url(fn ($record) => route('admin.offer.word', $record->id))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->hiddenLabel()
                    ->tooltip('Podgląd')
                    ->modalWidth('6xl')
                    ->modalContent(fn ($record) => view('filament.modals.offer-preview', ['offer' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
