<?php

namespace App\Filament\Resources\TaskResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $recordTitleAttribute = 'content';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\RichEditor::make('content')
                    ->label('Treść')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('recipient_ids')
                    ->label('Adresaci (wielu)')
                    ->options(User::query()->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->multiple()
                    ->placeholder('Wybierz adresatów (opcjonalnie)')
                    ->nullable()
                    ->default(function ($record) {
                        return $record?->recipients?->pluck('id')->toArray() ?? [];
                    })
                    ->helperText('Możesz wybrać jednego lub więcej adresatów. Jeśli nie wybierzesz, komentarz będzie widoczny dla wszystkich związanych z zadaniem.'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('content')
            ->modifyQueryUsing(fn ($query) => $query
                ->whereNull('parent_id')
                ->visibleTo(\Illuminate\Support\Facades\Auth::id())
                ->with(['author', 'recipients', 'replies.author', 'replies.recipients'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('author.name')
                    ->label('Autor')
                    ->sortable()
                    ->default('—'),
                Tables\Columns\TextColumn::make('recipients_list')
                    ->label('Adresaci')
                    ->getStateUsing(function ($record) {
                        $recipients = $record->recipients;
                        return $recipients && $recipients->count() > 0 
                            ? $recipients->pluck('name')->join(', ') 
                            : '—';
                    }),
                Tables\Columns\TextColumn::make('content')
                    ->label('Treść')
                    ->html()
                    ->limit(100)
                    ->description(function ($record) {
                        $repliesCount = $record->replies?->count() ?? 0;
                        return $repliesCount > 0 ? "💬 {$repliesCount} " . ($repliesCount === 1 ? 'odpowiedź' : ($repliesCount < 5 ? 'odpowiedzi' : 'odpowiedzi')) : null;
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data utworzenia')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nowy komentarz')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['author_id'] = Auth::id();
                        return $data;
                    })
                    ->after(function (Tables\Actions\CreateAction $action, $record): void {
                        $data = $action->getFormData();
                        $recipientIds = collect($data['recipient_ids'] ?? [])
                            ->filter()
                            ->map(fn ($id) => (int) $id)
                            ->unique()
                            ->values();

                        if ($recipientIds->isNotEmpty()) {
                            $record->recipients()->sync($recipientIds->all());
                            $record->recipient_id = $recipientIds->first();
                            $record->save();
                        }

                        app(\App\Services\Tasks\TaskCommentNotificationService::class)
                            ->notify($record, $recipientIds->all());
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('reply')
                    ->label('Odpowiedz')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->form([
                        Forms\Components\RichEditor::make('content')
                            ->label('Treść odpowiedzi')
                            ->required(),
                        Forms\Components\Select::make('recipient_ids')
                            ->label('Adresaci odpowiedzi')
                            ->options(User::query()->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->multiple()
                            ->placeholder('Wybierz adresatów (opcjonalnie)')
                            ->nullable()
                            ->helperText('Możesz wybrać konkretnych adresatów dla odpowiedzi. Jeśli nie wybierzesz, odpowiedź będzie widoczna dla autora komentarza i adresatów komentarza głównego.'),
                    ])
                    ->action(function ($record, array $data): void {
                        $reply = $record->replies()->create([
                            'task_id' => $record->task_id,
                            'author_id' => Auth::id(),
                            'content' => $data['content'],
                        ]);

                        $recipientIds = collect($data['recipient_ids'] ?? [])
                            ->filter()
                            ->map(fn ($id) => (int) $id)
                            ->unique()
                            ->values();

                        if ($recipientIds->isNotEmpty()) {
                            $reply->recipients()->sync($recipientIds->all());
                            $reply->recipient_id = $recipientIds->first();
                            $reply->save();
                        }

                        app(\App\Services\Tasks\TaskCommentNotificationService::class)
                            ->notify($reply, $recipientIds->all());
                    })
                    ->successNotificationTitle('Odpowiedź dodana'),
                Tables\Actions\Action::make('view_thread')
                    ->label('Zobacz wątek')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->modalHeading(fn ($record) => 'Wątek rozmowy')
                    ->modalContent(fn ($record) => view('filament.resources.task-resource.relation-managers.comments-thread', [
                        'comment' => $record->load('replies.author', 'replies.recipients', 'author', 'recipients'),
                    ]))
                    ->modalSubmitAction(false)
                    ->visible(fn ($record) => $record->replies?->count() > 0),
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => $record->author_id === Auth::id())
                    ->after(function (Tables\Actions\EditAction $action, $record): void {
                        $data = $action->getFormData();
                        $recipientIds = collect($data['recipient_ids'] ?? [])
                            ->filter()
                            ->map(fn ($id) => (int) $id)
                            ->unique()
                            ->values();

                        if ($recipientIds->isNotEmpty()) {
                            $record->recipients()->sync($recipientIds->all());
                            $record->recipient_id = $recipientIds->first();
                            $record->save();
                        }
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => $record->author_id === Auth::id()),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
} 