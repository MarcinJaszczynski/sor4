<?php

namespace App\Filament\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\Task;
use App\Models\TaskStatus;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class TasksKanbanBoard extends KanbanBoard
{
    protected static string $model = Task::class;

    protected static ?string $navigationIcon = 'heroicon-o-view-columns';
    protected static ?string $navigationLabel = 'Tablica Zadań';
    protected static ?string $navigationGroup = 'Zadania';
    protected static ?int $navigationSort = 10;
    protected static ?string $title = 'Tablica Zadań';

    public static function getNavigationUrl(): string
    {
        // Legacy link fix: sidebar should lead to the Task resource (/admin/tasks)
        return TaskResource::getUrl('index');
    }

    public function mount(): void
    {
        // Legacy route fix: if someone enters /admin/tasks-kanban-board, redirect to /admin/tasks
        $this->redirect(TaskResource::getUrl('index'));
    }

    protected static string $statusView = 'filament.kanban.status';
    protected static string $headerView = 'filament.kanban.header';

    protected function statuses(): Collection
    {
        return TaskStatus::orderBy('order')->get()->map(function ($status) {
            $color = str_replace('#', '', $status->color ?? 'gray');
            
            // Map colors to light "lekkie" variants
            $styles = match($color) {
                'blue' => [
                    'bg_class' => 'bg-blue-50/50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800',
                    'text_class' => 'text-blue-700 dark:text-blue-400',
                ],
                'green' => [
                    'bg_class' => 'bg-emerald-50/50 dark:bg-emerald-900/10 border-emerald-200 dark:border-emerald-800',
                    'text_class' => 'text-emerald-700 dark:text-emerald-400',
                ],
                'yellow', 'amber' => [
                    'bg_class' => 'bg-amber-50/50 dark:bg-amber-900/10 border-amber-200 dark:border-amber-800',
                    'text_class' => 'text-amber-700 dark:text-amber-400',
                ],
                'red', 'danger' => [
                    'bg_class' => 'bg-rose-50/50 dark:bg-rose-900/10 border-rose-200 dark:border-rose-800',
                    'text_class' => 'text-rose-700 dark:text-rose-400',
                ],
                default => [
                    'bg_class' => 'bg-gray-50/50 dark:bg-gray-900/20 border-gray-200 dark:border-gray-800',
                    'text_class' => 'text-gray-600 dark:text-gray-400',
                ],
            };

            return [
                'id' => $status->id,
                'title' => $status->name,
                'bg_class' => $styles['bg_class'],
                'text_class' => $styles['text_class'],
            ];
        });
    }

    protected function records(): Collection
    {
        // Return full Eloquent models (with relations eager loaded where useful)
        return Task::with(['status', 'assignee', 'author', 'subtasks', 'attachments', 'comments'])
            ->visibleTo(Auth::id())
            ->orderBy('order')
            ->get();
    }

    public function onStatusChanged(string|int $recordId, string $status, array $fromOrderedIds, array $toOrderedIds): void
    {
        Task::find($recordId)->update(['status_id' => $status]);
        
        Task::setNewOrder($toOrderedIds);
    }

    protected function getEditModalFormSchema(null|int|string $recordId): array
    {
        return [
            \Filament\Forms\Components\TextInput::make('title')
                ->label('Tytuł')
                ->required(),
            \Filament\Forms\Components\Select::make('status_id')
                ->label('Status')
                ->options(TaskStatus::all()->pluck('name', 'id'))
                ->required(),
            \Filament\Forms\Components\Select::make('priority')
                ->label('Priorytet')
                ->options([
                    'low' => 'Niski',
                    'medium' => 'Średni',
                    'high' => 'Wysoki',
                ])
                ->required(),
            \Filament\Forms\Components\RichEditor::make('description')
                ->label('Opis'),
            \Filament\Forms\Components\Repeater::make('attachments')
                ->label('Załączniki')
                ->relationship()
                ->schema([
                    \Filament\Forms\Components\FileUpload::make('file_path')
                        ->label('Plik')
                        ->disk('public')
                        ->directory('task-attachments')
                        ->preserveFilenames()
                        ->storeFileNamesIn('name')
                        ->openable()
                        ->downloadable(),
                    \Filament\Forms\Components\Hidden::make('user_id')
                        ->default(Auth::id()),
                ])
                ->columnSpanFull()
                ->addActionLabel('Dodaj plik')
                ->deleteAction(
                    fn (\Filament\Forms\Components\Actions\Action $action) => $action->requiresConfirmation(),
                )
                ->collapsible(),
        ];
    }
}
