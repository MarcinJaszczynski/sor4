<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PilotExpenseResource\Pages;
use App\Filament\Resources\PilotExpenseResource\RelationManagers;
use App\Models\PilotExpense;
use App\Models\EventProgramPoint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PilotExpenseResource extends Resource
{
    protected static ?string $model = PilotExpense::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finanse';
    protected static ?string $modelLabel = 'Wydatek Pilota';
    protected static ?string $pluralModelLabel = 'Wydatki Pilotów';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->relationship('event', 'name')
                            ->required()
                            ->searchable()
                            ->live()
                            ->columnSpan(3),
                        Forms\Components\Select::make('event_program_point_id')
                            ->label('Punkt programu')
                            ->options(fn (Forms\Get $get) =>
                                $get('event_id')
                                    ? EventProgramPoint::query()
                                        ->with('templatePoint')
                                        ->where('event_id', $get('event_id'))
                                        ->orderBy('day')
                                        ->orderBy('order')
                                        ->get()
                                        ->mapWithKeys(fn ($point) => [
                                            $point->id => ($point->templatePoint?->name ?? $point->name ?? ('#' . $point->id)) . ' (Dzień ' . $point->day . ')',
                                        ])
                                        ->toArray()
                                    : []
                            )
                            ->searchable()
                            ->columnSpan(3),
                        Forms\Components\Select::make('user_id')
                            ->label('Pilot')
                            ->relationship('user', 'name')
                            ->required()
                            ->columnSpan(3),
                        
                        Forms\Components\TextInput::make('amount')
                            ->label('Kwota')
                            ->numeric()
                            ->required()
                            ->columnSpan(2),
                        Forms\Components\TextInput::make('currency')
                            ->label('Waluta')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\DatePicker::make('expense_date')
                            ->label('Data wydatku')
                            ->required()
                            ->columnSpan(1),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Oczekuje',
                                'approved' => 'Zatwierdzony',
                                'rejected' => 'Odrzucony',
                            ])
                            ->required()
                            ->columnSpan(2),
                        
                        Forms\Components\Textarea::make('description')
                            ->label('Opis')
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('document_image')
                            ->label('Zdjęcie/Skan')
                            ->image()
                            ->directory('pilot-expenses')
                            ->columnSpanFull(),
                    ])->columns(6)->compact(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('expense_date')
                    ->label('Data')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('event.name')
                    ->label('Impreza')
                    ->searchable(),
                Tables\Columns\TextColumn::make('eventProgramPoint')
                    ->label('Punkt programu')
                    ->formatStateUsing(fn ($record) => $record->eventProgramPoint?->templatePoint?->name
                        ?? $record->eventProgramPoint?->name
                        ?? '—')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pilot')
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Kwota')
                    ->money(fn ($record) => $record->currency)
                    ->sortable(),
                Tables\Columns\SelectColumn::make('status')
                    ->options([
                        'pending' => 'Oczekuje',
                        'approved' => 'Zatwierdzony',
                        'rejected' => 'Odrzucony',
                    ]),
                Tables\Columns\ImageColumn::make('document_image')
                    ->label('Dokument'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Oczekuje',
                        'approved' => 'Zatwierdzony',
                        'rejected' => 'Odrzucony',
                    ]),
                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPilotExpenses::route('/'),
            'create' => Pages\CreatePilotExpense::route('/create'),
            'view' => Pages\ViewPilotExpense::route('/{record}'),
            'edit' => Pages\EditPilotExpense::route('/{record}/edit'),
        ];
    }
}
