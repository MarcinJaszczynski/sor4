<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmailMessageResource\Pages;
use App\Filament\Resources\EmailMessageResource\RelationManagers;
use App\Models\EmailMessage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmailMessageResource extends Resource
{
    protected static ?string $model = EmailMessage::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    protected static ?string $navigationGroup = 'Poczta';
    protected static ?int $navigationSort = 10;
    protected static ?string $navigationLabel = 'Poczta';
    protected static ?string $modelLabel = 'Wiadomość';
    protected static ?string $pluralModelLabel = 'Wiadomości';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Group::make([
                            Forms\Components\Section::make('Szczegóły wiadomości')
                                ->schema([
                                    Forms\Components\Select::make('email_account_id')
                                        ->label('Konto')
                                        ->relationship('account', 'account_name')
                                        ->disabled()
                                        ->columnSpan(2),
                                    Forms\Components\TextInput::make('from_address')
                                        ->label('Od')
                                        ->disabled()
                                        ->columnSpan(2),
                                    Forms\Components\TextInput::make('to_address')
                                        ->label('Do')
                                        ->disabled()
                                        ->columnSpan(2),
                                    Forms\Components\TextInput::make('subject')
                                        ->label('Temat')
                                        ->disabled()
                                        ->columnSpanFull(),
                                ])->columns(6)->compact(),

                            Forms\Components\Section::make('Treść')
                                ->schema([
                                    Forms\Components\ViewField::make('body_html')
                                        ->view('filament.forms.components.email-body')
                                        ->columnSpanFull(),
                                ])->compact(),
                        ])->columnSpan(2),

                        Forms\Components\Group::make([
                            Forms\Components\Section::make('Powiązania')
                                ->schema([
                                    Forms\Components\Select::make('relatedEvents')
                                        ->label('Impreza')
                                        ->relationship('relatedEvents', 'name')
                                        ->multiple()
                                        ->searchable()
                                        ->preload(),
                                    Forms\Components\Select::make('relatedTasks')
                                        ->label('Zadanie')
                                        ->relationship('relatedTasks', 'title')
                                        ->multiple()
                                        ->searchable()
                                        ->preload(),
                                       Forms\Components\Select::make('sharedUsers')
                                           ->label('Udostępnij użytkownikom')
                                           ->multiple()
                                           ->relationship('sharedUsers', 'name')
                                           ->searchable()
                                           ->preload(),
                                ])->compact(),

                            Forms\Components\Section::make('Status')
                                ->schema([
                                    Forms\Components\TextInput::make('folder')
                                        ->label('Folder')
                                        ->disabled(),
                                    Forms\Components\Toggle::make('is_read')
                                        ->label('Przeczytane')
                                        ->disabled(),
                                    Forms\Components\DateTimePicker::make('date')
                                        ->label('Data')
                                        ->disabled(),
                                ])->compact(),
                        ])->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\IconColumn::make('is_read')
                    ->label('')
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope-open')
                    ->falseIcon('heroicon-o-envelope')
                    ->trueColor('gray')
                    ->falseColor('primary'),
                Tables\Columns\TextColumn::make('from_address')
                    ->label('Nadawca')
                    ->description(fn (EmailMessage $record): ?string => $record->from_name)
                    ->searchable(['from_address', 'from_name'])
                    ->limit(30),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Temat')
                    ->searchable()
                    ->weight(fn (EmailMessage $record): string => $record->is_read ? 'normal' : 'bold'),
                Tables\Columns\TextColumn::make('account.account_name')
                    ->label('Konto')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('folder')
                    ->label('Folder')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'inbox' => 'success',
                        'sent' => 'info',
                        'draft' => 'warning',
                        'trash' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('date')
                    ->label('Data')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('account')
                    ->relationship('account', 'account_name'),
                Tables\Filters\SelectFilter::make('folder')
                    ->options([
                        'inbox' => 'Odebrane',
                        'sent' => 'Wysłane',
                        'draft' => 'Szkice',
                        'trash' => 'Kosz',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('reply')
                    ->label('Odpowiedz')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('success')
                    ->form([
                        Forms\Components\RichEditor::make('body')
                            ->label('Treść odpowiedzi')
                            ->required(),
                    ])
                    ->action(function (EmailMessage $record, array $data, \App\Services\EmailService $service) {
                        $service->send(
                            $record->account,
                            $record->from_address,
                            "Re: " . $record->subject,
                            $data['body']
                        );
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Wiadomość wysłana')
                            ->success()
                            ->send();
                    }),
                   Tables\Actions\Action::make('forward')
                       ->label('Prześlij dalej')
                       ->icon('heroicon-o-arrow-right')
                       ->color('primary')
                       ->form([
                           Forms\Components\TextInput::make('to')
                               ->label('Do')
                               ->placeholder('adres@przyklad.pl')
                               ->required(),
                           Forms\Components\RichEditor::make('body')
                               ->label('Treść')
                               ->default(fn (EmailMessage $record) => "FW: " . $record->subject . "\n\n--- Oryginał ---\n" . strip_tags($record->body_html)),
                       ])
                       ->action(function (EmailMessage $record, array $data, \App\Services\EmailService $service) {
                           $service->send(
                               $record->account,
                               $data['to'],
                               "Fwd: " . $record->subject,
                               $data['body']
                           );

                           \Filament\Notifications\Notification::make()
                               ->title('Wiadomość przesłana dalej')
                               ->success()
                               ->send();
                       }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\EmailMessageResource\RelationManagers\AttachmentsRelationManager::class,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function ($query) {
                $query->whereHas('account', function ($q) {
                    $q->forUser(auth()->user());
                })
                ->orWhereHas('sharedUsers', function ($q) {
                    $q->where('users.id', auth()->id());
                });
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmailMessages::route('/'),
            'create' => Pages\CreateEmailMessage::route('/create'),
            'edit' => Pages\EditEmailMessage::route('/{record}/edit'),
        ];
    }
}
