<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TaskComment;
use App\Models\User;

echo "Naprawiam komentarze bez autora...\n";

// Pobierz pierwszego użytkownika (admin) jako domyślnego autora
$defaultUser = User::first();

if (!$defaultUser) {
    echo "BŁĄD: Brak użytkowników w systemie!\n";
    exit(1);
}

echo "Domyślny autor: {$defaultUser->name} (ID: {$defaultUser->id})\n";

// Znajdź wszystkie komentarze bez autora
$commentsWithoutAuthor = TaskComment::whereNull('user_id')->get();

echo "Znaleziono {$commentsWithoutAuthor->count()} komentarzy bez autora.\n";

foreach ($commentsWithoutAuthor as $comment) {
    $comment->user_id = $defaultUser->id;
    $comment->save();
    echo "  - Zaktualizowano komentarz ID: {$comment->id}\n";
}

echo "\nGOTOWE! Wszystkie komentarze mają teraz autora.\n";
