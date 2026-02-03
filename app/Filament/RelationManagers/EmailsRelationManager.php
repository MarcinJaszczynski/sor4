<?php

namespace App\Filament\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmailsRelationManager extends RelationManager
{
    protected static string $relationship = 'emails';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('subject')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('subject')
            ->columns([
                Tables\Columns\TextColumn::make('from_address')
                    ->label('Od')
                    ->limit(20),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Temat')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date')
                    ->label('Data')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\Action::make('send')
                    ->label('Wyślij e-mail')
                    ->icon('heroicon-o-paper-airplane')
                    ->form([
                        Forms\Components\Select::make('email_account_id')
                            ->label('Z konta')
                            ->options(\App\Models\EmailAccount::pluck('account_name', 'id'))
                            ->required(),
                        Forms\Components\TextInput::make('to')
                            ->label('Do')
                            ->email()
                            ->required(),
                        Forms\Components\TextInput::make('subject')
                            ->label('Temat')
                            ->default(fn () => "Dotyczy: " . ($this->getOwnerRecord()->name ?? $this->getOwnerRecord()->title) . " [ID: " . $this->getOwnerRecord()->id . "]")
                            ->required(),
                        Forms\Components\RichEditor::make('body')
                            ->label('Treść')
                            ->required(),
                    ])
                    ->action(function (array $data, \App\Services\EmailService $service) {
                        $account = \App\Models\EmailAccount::find($data['email_account_id']);
                        $service->send(
                            $account,
                            $data['to'],
                            $data['subject'],
                            $data['body'],
                            [],
                            $this->getOwnerRecord()
                        );
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Wiadomość wysłana i przypisana')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\AttachAction::make()
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => \App\Filament\Resources\EmailMessageResource::getUrl('edit', ['record' => $record])),
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make(),
                ]),
            ]);
    }
}
