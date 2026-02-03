<?php

namespace App\Filament\Resources\ContractResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')->label('Imię')->required(),
                Forms\Components\TextInput::make('last_name')->label('Nazwisko')->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'active' => 'Aktywny',
                        'cancelled' => 'Zrezygnował',
                    ])
                    ->required()
                    ->default('active')
                    ->live(),
                Forms\Components\Group::make([
                    Forms\Components\TextInput::make('cancellation_fee')
                        ->label('Koszt rezygnacji')
                        ->numeric()
                        ->suffix('PLN')
                        ->default(0),
                    Forms\Components\DatePicker::make('cancellation_date')
                        ->label('Data rezygnacji')
                        ->default(now()),
                    Forms\Components\Textarea::make('cancellation_reason')
                        ->label('Powód rezygnacji')
                        ->columnSpanFull(),
                ])
                ->visible(fn (\Filament\Forms\Get $get) => $get('status') === 'cancelled')
                ->columns(2)
                ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('last_name')
            ->columns([
                Tables\Columns\TextColumn::make('first_name')->label('Imię'),
                Tables\Columns\TextColumn::make('last_name')->label('Nazwisko'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'cancelled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('cancellation_fee')
                    ->label('Koszt rezygnacji')
                    ->money('PLN')
                    ->visible(fn ($livewire) => $livewire->getOwnerRecord()->participants()->where('status', 'cancelled')->exists()),
            ])
            ->filters([
                //
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
