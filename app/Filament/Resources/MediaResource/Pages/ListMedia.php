<?php

namespace App\Filament\Resources\MediaResource\Pages;

use App\Filament\Resources\MediaResource;
use Filament\Resources\Pages\ListRecords;

class ListMedia extends ListRecords
{
    protected static string $resource = MediaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()->label('Dodaj Plik'),
            \Filament\Actions\Action::make('sync')
                ->label('Synchronizuj z dysku')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Synchronizacja plików')
                ->modalDescription('Ta operacja przeszuka folder "public" i doda brakujące pliki do biblioteki mediów. Może to chwilę potrwać.')
                ->action(function () {
                    $disk = \Illuminate\Support\Facades\Storage::disk('public');
                    // Get all files recursively
                    $files = $disk->allFiles();
                    
                    $count = 0;
                    foreach ($files as $file) {
                        // Skip hidden files or system files
                        if (str_starts_with(basename($file), '.')) continue;
                        if ($file === 'gitignore') continue;

                        // Check existence
                        $exists = \App\Models\Media::where('path', $file)->exists();
                        if (!$exists) {
                            try {
                                $media = new \App\Models\Media();
                                $media->disk = 'public';
                                $media->path = $file; // This triggers the mutator to calculate metadata
                                $media->save();
                                $count++;
                            } catch (\Exception $e) {
                                // Ignore errors for individual files
                            }
                        }
                    }
                    
                    \Filament\Notifications\Notification::make()
                        ->title("Zsynchronizowano {$count} nowych plików.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
