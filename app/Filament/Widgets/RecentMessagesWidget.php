<?php

namespace App\Filament\Widgets;

use App\Models\Conversation;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class RecentMessagesWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 1;
    
    protected static ?string $heading = 'Ostatnie Wiadomości';

    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(function() {
                // Konwersacje gdzie user jest uczestnikiem
                return Conversation::whereHas('participants', function ($q) {
                    $q->where('user_id', Auth::id());
                })
                ->orderBy('last_message_at', 'desc')
                ->limit(5);
            })
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Temat')
                    ->limit(20)
                    ->description(fn (Conversation $record): string => $record->messages()->latest()->first()?->content ?? ''),
                
                Tables\Columns\TextColumn::make('last_message_at')
                    ->label('Kiedy')
                    ->since()
                    ->color('gray')
                    ->size('xs'),
            ])
            ->actions([
                Tables\Actions\Action::make('reply')
                    ->label('')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->url(fn (Conversation $record): string => \App\Filament\Resources\ConversationResource::getUrl('edit', ['record' => $record])),
            ])
            ->paginated(false);
    }
}
