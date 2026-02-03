<?php

namespace App\Filament\Resources\ContractResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AddendumsRelationManager extends RelationManager
{
    protected static string $relationship = 'addendums';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('addendum_number')
                    ->label('Numer Aneksu')
                    ->required()
                    ->maxLength(255)
                    ->default(function (AddendumsRelationManager $livewire) {
                        $contract = $livewire->getOwnerRecord();
                        $count = $contract->addendums()->count() + 1;
                        return $contract->contract_number . '/A' . $count;
                    }),
                Forms\Components\DatePicker::make('date_issued')
                    ->label('Data wystawienia')
                    ->default(now())
                    ->columnSpan(1)
                    ->required(),
                Forms\Components\TextInput::make('locked_price_per_person')
                    ->label('Zablokowana cena za osobę (Aneks)')
                    ->numeric()
                    ->suffix('PLN')
                    ->columnSpan(1)
                    ->default(fn (AddendumsRelationManager $livewire) => $livewire->getOwnerRecord()->locked_price_per_person),
                Forms\Components\TextInput::make('amount_change')
                    ->label('Zmiana kwoty (+/-)')
                    ->numeric()
                    ->default(0)
                    ->suffix('PLN'),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Szkic',
                        'generated' => 'Zatwierdzony',
                        'cancelled' => 'Anulowany',
                    ])
                    ->default('draft')
                    ->required(),
                Forms\Components\Textarea::make('changes_summary')
                    ->label('Podsumowanie zmian')
                    ->columnSpanFull(),
                Forms\Components\RichEditor::make('content')
                    ->label('Treść Aneksu')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('addendum_number')
            ->columns([
                Tables\Columns\TextColumn::make('addendum_number')
                    ->label('Numer'),
                Tables\Columns\TextColumn::make('date_issued')
                    ->label('Data')
                    ->date(),
                Tables\Columns\TextColumn::make('amount_change')
                    ->label('Zmiana Kwoty')
                    ->money('PLN'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'generated' => 'success',
                        'cancelled' => 'danger',
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->label('Drukuj')
                    ->icon('heroicon-o-printer')
                    ->url(fn ($record) => route('client.addendum', ['uuid' => $record->contract->uuid, 'addendum' => $record->id]))
                    ->openUrlInNewTab(),
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
