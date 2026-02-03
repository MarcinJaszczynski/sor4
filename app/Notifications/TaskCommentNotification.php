<?php

namespace App\Notifications;

                use Illuminate\Bus\Queueable;
                use Illuminate\Notifications\Notification;

                class TaskCommentNotification extends Notification
                {
                    use Queueable;

                    public function __construct(
                        protected int $taskId,
                        protected string $taskTitle,
                        protected int $commentId,
                        protected string $commentExcerpt,
                        protected int $authorId,
                        protected string $authorName,
                        protected int $recipientId,
                        protected string $recipientName,
                        protected array $recipientNames = [],
                        protected ?string $eventName = null,
                        protected ?string $eventCode = null,
                        protected ?string $taskableType = null,
                        protected ?int $taskableId = null,
                        protected ?string $taskAuthorName = null,
                        protected ?string $taskAssigneeName = null,
                        protected ?int $parentCommentId = null,
                        protected ?string $parentExcerpt = null,
                    ) {}

                    public function via(object $notifiable): array
                    {
                        return ['database'];
                    }

                    public function toDatabase(object $notifiable): array
                    {
                        return [
                            'type' => 'task_comment',
                            'task_id' => $this->taskId,
                            'task_title' => $this->taskTitle,
                            'comment_id' => $this->commentId,
                            'comment_excerpt' => $this->commentExcerpt,
                            'author_id' => $this->authorId,
                            'author_name' => $this->authorName,
                            'recipient_id' => $this->recipientId,
                            'recipient_name' => $this->recipientName,
                            'recipient_names' => $this->recipientNames,
                            'event_name' => $this->eventName,
                            'event_code' => $this->eventCode,
                            'taskable_type' => $this->taskableType,
                            'taskable_id' => $this->taskableId,
                            'task_author_name' => $this->taskAuthorName,
                            'task_assignee_name' => $this->taskAssigneeName,
                            'parent_comment_id' => $this->parentCommentId,
                            'parent_excerpt' => $this->parentExcerpt,
                        ];
                    }
                }
