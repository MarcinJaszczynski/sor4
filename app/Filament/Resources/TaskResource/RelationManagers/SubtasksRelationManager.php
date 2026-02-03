<?php

namespace App\Filament\Resources\TaskResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SubtasksRelationManager extends RelationManager
{
    protected static string $relationship = 'subtasks';
    
    protected static ?string $title = 'Podzadania';
    
    protected static ?string $modelLabel = 'podzadanie';
    
    protected static ?string $pluralModelLabel = 'podzadania';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Tytuł')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),
                    
                Forms\Components\Select::make('status_id')
                    ->label('Status')
                    ->relationship('status', 'name')
                    ->default(1)
                    ->required()
                    ->columnSpan(1),
                    
                Forms\Components\Select::make('priority')
                    ->label('Priorytet')
                    ->options([
                        'low' => 'Niski',
                        'medium' => 'Średni',
                        'high' => 'Wysoki',
                    ])
                    ->default('medium')
                    ->required()
                    ->columnSpan(1),
                    
                Forms\Components\DateTimePicker::make('due_date')
                    ->label('Termin')
                    ->columnSpan(2),
                    
                Forms\Components\Select::make('assignees')
                    ->label('Przypisane do')
                    ->relationship('assignees', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpan(2),
                    
                Forms\Components\RichEditor::make('description')
                    ->label('Opis')
                    ->columnSpanFull(),
                    
                Forms\Components\Hidden::make('author_id')
                    ->default(auth()->id()),
            ])
            ->columns(4);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Tytuł')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('status.name')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('assignees.name')
                    ->label('Przypisane do')
                    ->badge()
                    ->color('info')
                    ->separator(', '),
                    
                Tables\Columns\TextColumn::make('priority')
                    ->label('Priorytet')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'low' => 'gray',
                        'medium' => 'warning',
                        'high' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'low' => 'Niski',
                        'medium' => 'Średni',
                        'high' => 'Wysoki',
                    }),
                    
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Termin')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->relationship('status', 'name'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Dodaj podzadanie')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['author_id'] = auth()->id();
                        // Dziedzicz taskable z zadania nadrzędnego jeśli jest
                        $parent = $this->getOwnerRecord();
                        if ($parent && $parent->taskable_type && $parent->taskable_id) {
                            $data['taskable_type'] = $parent->taskable_type;
                            $data['taskable_id'] = $parent->taskable_id;
                        }
                        return $data;
                    }),
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
