<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TaskResource\Pages;
use App\Filament\Resources\TaskResource\RelationManagers;
use App\Models\Task;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaskResource extends Resource
{
    protected static ?string $model = Task::class;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Kontekst zadania')
                            ->schema([
                                Forms\Components\Placeholder::make('author_info')
                                    ->label('Autor zadania')
                                    ->content(function ($record) {
                                        if (!$record || !$record->author) {
                                            return 'Brak informacji';
                                        }
                                        return $record->author->name;
                                    })
                                    ->columnSpan(2),
                                
                                Forms\Components\Placeholder::make('parent_info')
                                    ->label('Zadanie nadrzędne')
                                    ->content(function ($record) {
                                        if (!$record || !$record->parent_id || !$record->parent) {
                                            return null;
                                        }
                                        $parent = $record->parent;
                                        $url = route('filament.admin.resources.tasks.edit', ['record' => $parent->id]);
                                        return new \Illuminate\Support\HtmlString(
                                            "<a href='{$url}' target='_blank' class='text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300'>
                                                #{$parent->id} - {$parent->title}
                                            </a>"
                                        );
                                    })
                                    ->visible(fn ($record) => $record && $record->parent_id)
                                    ->columnSpan(4),
                                
                                Forms\Components\Placeholder::make('taskable_event_template')
                                    ->label('Szablon imprezy')
                                    ->content(function ($record) {
                                        if (!$record || !$record->taskable) return null;
                                        
                                        $template = null;
                                        $taskable = $record->taskable;
                                        
                                        if ($taskable instanceof \App\Models\EventTemplate) {
                                            $template = $taskable;
                                        } elseif ($taskable instanceof \App\Models\EventTemplateProgramPoint && $taskable->eventTemplate) {
                                            $template = $taskable->eventTemplate;
                                        }
                                        
                                        if (!$template) return null;
                                        
                                        $url = route('filament.admin.resources.event-templates.edit', ['record' => $template->id]);
                                        return new \Illuminate\Support\HtmlString(
                                            "<a href='{$url}' target='_blank' class='text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300'>
                                                📋 {$template->name}
                                            </a>"
                                        );
                                    })
                                    ->visible(function ($record) {
                                        if (!$record || !$record->taskable) return false;
                                        $t = $record->taskable;
                                        return $t instanceof \App\Models\EventTemplate || 
                                               ($t instanceof \App\Models\EventTemplateProgramPoint && $t->eventTemplate);
                                    })
                                    ->columnSpan(3),
                                
                                Forms\Components\Placeholder::make('taskable_event')
                                    ->label('Impreza')
                                    ->content(function ($record) {
                                        if (!$record || !$record->taskable) return null;
                                        
                                        $event = null;
                                        $taskable = $record->taskable;
                                        
                                        if ($taskable instanceof \App\Models\Event) {
                                            $event = $taskable;
                                        } elseif ($taskable instanceof \App\Models\EventProgramPoint && $taskable->event) {
                                            $event = $taskable->event;
                                        } elseif ($taskable instanceof \App\Models\Contract && $taskable->event) {
                                            $event = $taskable->event;
                                        }
                                        
                                        if (!$event) return null;
                                        
                                        $url = route('filament.admin.resources.events.edit', ['record' => $event->id]);
                                        return new \Illuminate\Support\HtmlString(
                                            "<a href='{$url}' target='_blank' class='text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300'>
                                                🎉 {$event->name}
                                            </a>"
                                        );
                                    })
                                    ->visible(function ($record) {
                                        if (!$record || !$record->taskable) return false;
                                        $t = $record->taskable;
                                        return $t instanceof \App\Models\Event || 
                                               ($t instanceof \App\Models\EventProgramPoint && $t->event) ||
                                               ($t instanceof \App\Models\Contract && $t->event);
                                    })
                                    ->columnSpan(3),
                                
                                Forms\Components\Placeholder::make('taskable_point')
                                    ->label('Punkt programu')
                                    ->content(function ($record) {
                                        if (!$record || !$record->taskable) return null;
                                        
                                        $taskable = $record->taskable;
                                        
                                        if ($taskable instanceof \App\Models\EventProgramPoint) {
                                            $event = $taskable->event;
                                            if ($event) {
                                                $url = route('filament.admin.resources.events.edit', ['record' => $event->id]) . '#program';
                                                return new \Illuminate\Support\HtmlString(
                                                    "<a href='{$url}' target='_blank' class='text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300'>
                                                        📍 {$taskable->name}
                                                    </a>"
                                                );
                                            }
                                        } elseif ($taskable instanceof \App\Models\EventTemplateProgramPoint) {
                                            $template = $taskable->eventTemplate;
                                            if ($template) {
                                                $url = route('filament.admin.resources.event-templates.edit', ['record' => $template->id]) . '#program';
                                                return new \Illuminate\Support\HtmlString(
                                                    "<a href='{$url}' target='_blank' class='text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300'>
                                                        📍 {$taskable->name}
                                                    </a>"
                                                );
                                            }
                                        }
                                        
                                        return null;
                                    })
                                    ->visible(function ($record) {
                                        if (!$record || !$record->taskable) return false;
                                        $t = $record->taskable;
                                        return $t instanceof \App\Models\EventProgramPoint || 
                                               $t instanceof \App\Models\EventTemplateProgramPoint;
                                    })
                                    ->columnSpan(6),
                                
                                Forms\Components\Placeholder::make('taskable_contract')
                                    ->label('Umowa')
                                    ->content(function ($record) {
                                        if (!$record || !$record->taskable) return null;
                                        
                                        $taskable = $record->taskable;
                                        
                                        if ($taskable instanceof \App\Models\Contract) {
                                            $url = route('filament.admin.resources.contracts.edit', ['record' => $taskable->id]);
                                            return new \Illuminate\Support\HtmlString(
                                                "<a href='{$url}' target='_blank' class='text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300'>
                                                    📄 {$taskable->contract_number}
                                                </a>"
                                            );
                                        }
                                        
                                        return null;
                                    })
                                    ->visible(function ($record) {
                                        if (!$record || !$record->taskable) return false;
                                        return $record->taskable instanceof \App\Models\Contract;
                                    })
                                    ->columnSpan(3),
                            ])
                            ->columns(6)
                            ->compact()
                            ->visible(fn ($record) => $record),

                        Forms\Components\Section::make('Szczegóły zadania')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->label('Tytuł')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(3),
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
                                    ->required()
                                    ->columnSpan(1),
                                Forms\Components\DateTimePicker::make('due_date')
                                    ->label('Termin')
                                    ->columnSpan(1),
                                Forms\Components\RichEditor::make('description')
                                    ->label('Opis')
                                    ->columnSpanFull(),
                            ])
                            ->columns(6)
                            ->compact(),

                        Forms\Components\Section::make('Przypisanie')
                            ->schema([
                                Forms\Components\Select::make('assignees')
                                    ->label('Przypisane do')
                                    ->relationship('assignees', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->columnSpan(6),
                                
                                Forms\Components\Hidden::make('parent_id'),
                                Forms\Components\Hidden::make('taskable_type'),
                                Forms\Components\Hidden::make('taskable_id'),
                            ])
                            ->columns(6)
                            ->compact(),
                    ])
                    ->columnSpan(['lg' => 2]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Załączniki')
                            ->schema([
                                Forms\Components\Repeater::make('attachments')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\FileUpload::make('file_path')
                                            ->label('Plik')
                                            ->disk('public')
                                            ->directory('task-attachments')
                                            ->preserveFilenames()
                                            ->storeFileNamesIn('name')
                                            ->openable()
                                            ->downloadable(),
                                        Forms\Components\Hidden::make('user_id')
                                            ->default(auth()->id()),
                                    ])
                                    ->defaultItems(0)
                                    ->addActionLabel('Dodaj plik')
                                    ->deleteAction(
                                        fn (Forms\Components\Actions\Action $action) => $action->requiresConfirmation(),
                                    )
                                    ->collapsible()
                                    ->collapsed(fn ($state) => count($state ?? []) > 0),
                            ])
                            ->compact(),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->defaultSort('order')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Tytuł')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status.name')
                    ->label('Status')
                    ->sortable()
                    ->badge(),
                Tables\Columns\TextColumn::make('assignees.name')
                    ->label('Przypisane do')
                    ->badge()
                    ->color('info')
                    ->separator(', '),
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Termin')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Priorytet')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->relationship('status', 'name'),
                Tables\Filters\SelectFilter::make('assignees')
                    ->label('Przypisane do')
                    ->relationship('assignees', 'name')
                    ->multiple()
                    ->preload(),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('subtasks')
                    ->label('Podzadania')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('warning')
                    ->modalContent(fn (Task $record) => view('livewire.task-manager-wrapper', ['taskable' => $record]))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\CommentsRelationManager::class,
            RelationManagers\AttachmentsRelationManager::class,
            RelationManagers\SubtasksRelationManager::class,
            \App\Filament\RelationManagers\EmailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\TasksKanbanBoard::route('/'),
            'create' => Pages\CreateTask::route('/create'),
            'edit' => Pages\EditTask::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Nie stosujemy scope visibleTo tutaj, ponieważ Policy TaskPolicy
        // obsługuje autoryzację na poziomie pojedynczych rekordów
        // Scope visibleTo jest używany tylko w Livewire komponentach dla list
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function canViewAny(): bool
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user && $user->roles && $user->roles->contains('name', 'admin')) {
            return true;
        }
        if ($user && $user->roles && $user->roles->flatMap->permissions->contains('name', 'view task')) {
            return true;
        }
        return false;
    }
}