<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestEventsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 2;
    
    protected static ?string $heading = 'Najbliższe Wyloty / Wyjazdy';

    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::query()
                    ->where('start_date', '>=', now())
                    ->orderBy('start_date', 'asc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Impreza')
                    ->searchable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Początek')
                    ->date('d.m.Y (D)'),
                
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Koniec')
                    ->date('d.m.Y'),

                Tables\Columns\TextColumn::make('participant_count')
                    ->label('Osób')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('bus.name')
                    ->label('Autokar')
                    ->default('-')
                    ->limit(20),
                
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Szczegóły')
                    ->url(fn (Event $record): string => \App\Filament\Resources\EventResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}
