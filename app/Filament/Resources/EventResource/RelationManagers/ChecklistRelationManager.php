<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use App\Models\EventChecklistItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChecklistRelationManager extends RelationManager
{
    protected static string $relationship = 'checklistItems';

    protected static ?string $title = 'Checklist organizacyjny';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('stage')
                    ->label('Etap')
                    ->options([
                        'Planowanie' => 'Planowanie',
                        'Umowy' => 'Umowy',
                        'Realizacja' => 'Realizacja',
                        'Rozliczenie' => 'Rozliczenie',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('label')
                    ->label('Zadanie')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_done')
                    ->label('Zrobione')
                    ->inline(false),
                Forms\Components\DateTimePicker::make('done_at')
                    ->label('Data ukończenia')
                    ->visible(fn (Forms\Get $get) => (bool) $get('is_done')),
                Forms\Components\TextInput::make('note')
                    ->label('Notatka')
                    ->maxLength(255),
                Forms\Components\Hidden::make('key')
                    ->default(fn () => 'custom_' . uniqid()),
                Forms\Components\Hidden::make('order')
                    ->default(999),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('label')
            ->columns([
                Tables\Columns\TextColumn::make('stage')
                    ->label('Etap')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('label')
                    ->label('Zadanie')
                    ->searchable()
                    ->description(fn ($record) => $record->note ?? null),
                Tables\Columns\ToggleColumn::make('is_done')
                    ->label('Zrobione')
                    ->afterStateUpdated(function (EventChecklistItem $record, bool $state): void {
                        $record->done_at = $state ? now() : null;
                        $record->save();
                    }),
                Tables\Columns\TextColumn::make('done_at')
                    ->label('Ukończono')
                    ->date('d.m.Y H:i')
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('order')
            ->filters([
                Tables\Filters\SelectFilter::make('stage')
                    ->label('Etap')
                    ->options([
                        'Planowanie' => 'Planowanie',
                        'Umowy' => 'Umowy',
                        'Realizacja' => 'Realizacja',
                        'Rozliczenie' => 'Rozliczenie',
                    ]),
                Tables\Filters\TernaryFilter::make('is_done')
                    ->label('Status')
                    ->placeholder('Wszystkie')
                    ->trueLabel('Zrobione')
                    ->falseLabel('Do zrobienia'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hiddenLabel(),
                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->hiddenLabel()
                    ->tooltip('Dodaj zadanie'),
            ]);
    }
}
