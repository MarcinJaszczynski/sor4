<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CalendarNoteResource\Pages;
use App\Models\CalendarNote;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CalendarNoteResource extends Resource
{
    protected static ?string $model = CalendarNote::class;

    protected static ?string $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?string $navigationGroup = 'Komunikacja';

    protected static ?string $navigationLabel = 'Notatki (kalendarz)';

    protected static ?int $navigationSort = 16;

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        return $data;
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($user->hasRole(['super_admin', 'admin', 'manager'])) {
            return true;
        }

        return (int) $record->user_id === (int) $user->id;
    }

    public static function canDelete(Model $record): bool
    {
        return static::canEdit($record);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('user_id'),

                Forms\Components\DatePicker::make('date')
                    ->label('Data')
                    ->required()
                    ->native(false),

                Forms\Components\TextInput::make('title')
                    ->label('Tytuł')
                    ->maxLength(255),

                Forms\Components\Textarea::make('content')
                    ->label('Treść')
                    ->rows(6)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $user = auth()->user();
                if (! $user) {
                    return $query->whereRaw('1=0');
                }

                if ($user->hasRole(['super_admin', 'admin', 'manager'])) {
                    return $query;
                }

                return $query->where('user_id', $user->id);
            })
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Data')
                    ->date('d.m.Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->label('Tytuł')
                    ->searchable()
                    ->wrap(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Autor')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Zmieniono')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCalendarNotes::route('/'),
            'create' => Pages\CreateCalendarNote::route('/create'),
            'edit' => Pages\EditCalendarNote::route('/{record}/edit'),
        ];
    }
}
