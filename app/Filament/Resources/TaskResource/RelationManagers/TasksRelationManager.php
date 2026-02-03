<?php

namespace App\Filament\Resources\TaskResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TasksRelationManager extends RelationManager
{
    protected static string $relationship = 'tasks';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Tytuł')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                
                Forms\Components\RichEditor::make('description')
                    ->label('Opis')
                    ->columnSpanFull(),

                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('status_id')
                            ->label('Status')
                            ->relationship('status', 'name')
                            ->default(1)
                            ->required(),

                        Forms\Components\Select::make('priority')
                            ->label('Priorytet')
                            ->options([
                                'low' => 'Niski',
                                'medium' => 'Średni',
                                'high' => 'Wysoki',
                            ])
                            ->required()
                            ->default('medium'),

                        Forms\Components\Select::make('assignee_id')
                            ->label('Przypisane do')
                            ->relationship('assignee', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\DateTimePicker::make('due_date')
                            ->label('Termin'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->paginationPageOptions([15])
            ->defaultPaginationPageOption(15)
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Tytuł')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status.name')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Nowe' => 'gray',
                        'W trakcie' => 'warning',
                        'Zakończone' => 'success',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Priorytet')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'high' => 'Wysoki',
                        'medium' => 'Średni',
                        'low' => 'Niski',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('assignee.name')
                    ->label('Przypisane do')
                    ->icon('heroicon-m-user-circle')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Termin')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->relationship('status', 'name')
                    ->label('Status'),
                
                Tables\Filters\SelectFilter::make('assignee')
                    ->relationship('assignee', 'name')
                    ->label('Przypisane do'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
