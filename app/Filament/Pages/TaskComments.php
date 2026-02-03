<?php

namespace App\Filament\Pages;

use App\Notifications\TaskCommentNotification;
use Filament\Pages\Page;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;

class TaskComments extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';
    protected static ?string $navigationLabel = 'Komentarze zadań';
    protected static ?string $navigationGroup = 'Komunikacja';
    protected static ?int $navigationSort = 2;
    protected static string $view = 'filament.pages.task-comments';
    protected static ?string $slug = 'task-comments';

    public function getTitle(): string
    {
        return 'Komentarze zadań';
    }

    public function getViewData(): array
    {
        $user = Auth::user();
        if (! $user) {
            return [
                'unread' => collect(),
                'recentRead' => collect(),
            ];
        }

        $unread = $user->unreadNotifications()
            ->where('type', TaskCommentNotification::class)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $recentRead = $user->readNotifications()
            ->where('type', TaskCommentNotification::class)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return [
            'unread' => $unread,
            'recentRead' => $recentRead,
        ];
    }
}
