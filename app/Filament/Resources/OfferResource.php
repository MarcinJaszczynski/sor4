<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfferResource\Pages;
use App\Models\Event;
use App\Models\Offer;
use App\Models\OfferTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OfferResource extends Resource
{
    protected static ?string $model = Offer::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Imprezy';
    protected static ?int $navigationSort = 25;
    protected static ?string $navigationLabel = 'Oferty';
    protected static ?string $modelLabel = 'Oferta';
    protected static ?string $pluralModelLabel = 'Oferty';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Szczegóły oferty')
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->label('Impreza')
                            ->relationship('event', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(3),

                        Forms\Components\TextInput::make('name')
                            ->label('Nazwa oferty')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(3),

                        Forms\Components\Select::make('offer_template_id')
                            ->label('Szablon')
                            ->relationship('template', 'name')
                            ->searchable()
                            ->preload()
                            ->default(fn () => OfferTemplate::query()->where('is_default', true)->value('id'))
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\RichEditor::make('introduction'),
                                Forms\Components\RichEditor::make('terms'),
                            ])
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                if (! $state) {
                                    return;
                                }

                                $template = OfferTemplate::query()->find($state);
                                if (! $template) {
                                    return;
                                }

                                $set('introduction', $template->introduction);
                                $set('terms', $template->terms);
                            })
                            ->columnSpan(3),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Szkic',
                                'sent' => 'Wysłana',
                                'accepted' => 'Zaakceptowana',
                                'rejected' => 'Odrzucona',
                            ])
                            ->default('draft')
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('participant_count')
                            ->label('Liczba uczestników')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\DatePicker::make('valid_until')
                            ->label('Ważna do')
                            ->default(now()->addDays(14))
                            ->columnSpan(1),
                    ])
                    ->columns(6)
                    ->compact(),

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

                Forms\Components\Section::make('Parametry cenowe')
                    ->description('Pola opcjonalne — możesz je wypełnić ręcznie lub wyliczać w przyszłej automatyzacji.')
                    ->schema([
                        Forms\Components\TextInput::make('cost_per_person')
                            ->label('Koszt / os.')
                            ->numeric()
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('price_per_person')
                            ->label('Cena / os.')
                            ->numeric()
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('total_price')
                            ->label('Cena całkowita')
                            ->numeric()
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('margin_percent')
                            ->label('Marża %')
                            ->numeric()
                            ->columnSpan(2),
                    ])
                    ->columns(6)
                    ->compact(),

                Forms\Components\Section::make('Punkty oferty')
                    ->description('Lista punktów generuje się automatycznie po utworzeniu oferty (na podstawie programu imprezy).')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship()
                            ->label('Lista punktów')
                            ->schema([
                                Forms\Components\Select::make('event_program_point_id')
                                    ->label('Punkt programu')
                                    ->relationship('programPoint', 'name')
                                    ->disabled()
                                    ->dehydrated()
                                    ->columnSpan(4),
                                Forms\Components\Toggle::make('is_optional')
                                    ->label('Opcjonalny')
                                    ->inline(false)
                                    ->columnSpan(1),
                                Forms\Components\Toggle::make('is_included')
                                    ->label('W cenie')
                                    ->inline(false)
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Ilość')
                                    ->numeric()
                                    ->columnSpan(1),
                                Forms\Components\TextInput::make('custom_price')
                                    ->label('Cena (nadpisz)')
                                    ->numeric()
                                    ->columnSpan(1),
                                Forms\Components\Textarea::make('custom_description')
                                    ->label('Opis (nadpisz)')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(8)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ])
                    ->hidden(fn (string $operation) => $operation === 'create')
                    ->compact(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nazwa')
                    ->searchable(),
                Tables\Columns\TextColumn::make('event.name')
                    ->label('Impreza')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Offer $record) => EventResource::getUrl('edit', ['record' => $record->event_id])),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'accepted' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('participant_count')
                    ->label('Osób')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Cena')
                    ->money('PLN')
                    ->placeholder('-')
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Ważna do')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Autor')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'name')
                    ->label('Impreza')
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Szkic',
                        'sent' => 'Wysłana',
                        'accepted' => 'Zaakceptowana',
                        'rejected' => 'Odrzucona',
                    ]),
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\Filter::make('valid_until')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Ważna od'),
                        Forms\Components\DatePicker::make('until')->label('Ważna do'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('valid_until', '>=', $date),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $query, $date): Builder => $query->whereDate('valid_until', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('pdf')
                    ->label('PDF')
                    ->icon('heroicon-o-printer')
                    ->url(fn (Offer $record) => route('admin.offer.pdf', $record->id))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('preview')
                    ->label('Podgląd')
                    ->icon('heroicon-o-eye')
                    ->modalWidth('6xl')
                    ->modalContent(fn (Offer $record) => view('filament.modals.offer-preview', ['offer' => $record])),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOffers::route('/'),
            'create' => Pages\CreateOffer::route('/create'),
            'edit' => Pages\EditOffer::route('/{record}/edit'),
        ];
    }
}
