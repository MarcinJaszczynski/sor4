<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CancellationsRelationManager extends RelationManager
{
    protected static string $relationship = 'cancellations';

    protected static ?string $title = 'Rezygnacje z imprezy';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dane rezygnacji')
                    ->schema([
                        Forms\Components\TextInput::make('qty')
                            ->label('Liczba osób')
                            ->numeric()
                            ->default(1)
                            ->required(),
                        Forms\Components\TextInput::make('amount')
                            ->label('Łączny koszt rezygnacji (opłata)')
                            ->numeric()
                            ->suffix('PLN')
                            ->default(0)
                            ->required(),
                        Forms\Components\DatePicker::make('cancellation_date')
                            ->label('Data rezygnacji')
                            ->default(now())
                            ->required(),
                    ])
                    ->columns(2)
                    ->compact(),
                
                Forms\Components\Section::make('Uzasadnienie')
                    ->schema([
                        Forms\Components\Textarea::make('reason')
                            ->label('Powód / Opis')
                            ->rows(3)
                            ->maxLength(255),
                    ])
                    ->collapsed()
                    ->compact(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason')
            ->columns([
                Tables\Columns\TextColumn::make('cancellation_date')
                    ->label('Data')
                    ->date('d.m.Y')
                    ->sortable()
                    ->description(fn ($record) => $record->reason ? mb_strimwidth($record->reason, 0, 50, '...') : null),
                Tables\Columns\TextColumn::make('qty')
                    ->label('Osób')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Koszt rezygnacji')
                    ->money('PLN')
                    ->alignEnd()
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->label('Suma')
                        ->money('PLN')
                    ),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Dodaj rezygnację'),
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
