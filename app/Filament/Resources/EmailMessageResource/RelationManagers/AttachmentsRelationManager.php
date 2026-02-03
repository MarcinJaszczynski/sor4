<?php

namespace App\Filament\Resources\EmailMessageResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Forms;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    protected static ?string $recordTitleAttribute = 'file_name';

    public function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('file_path')
                ->label('Plik')
                ->directory('email_attachments')
                ->disk('local')
                ->preserveFilenames()
                ->required(),
            Forms\Components\TextInput::make('file_name')
                ->label('Nazwa pliku')
                ->nullable(),
            Forms\Components\TextInput::make('mime_type')
                ->label('Typ MIME')
                ->disabled(),
            Forms\Components\TextInput::make('size')
                ->label('Rozmiar (bytes)')
                ->disabled(),
        ]);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('file_name')->label('Nazwa'),
            Tables\Columns\TextColumn::make('mime_type')->label('Typ'),
            Tables\Columns\TextColumn::make('size')->label('Rozmiar'),
            Tables\Columns\TextColumn::make('created_at')->label('Dodano')->dateTime(),
        ])->actions([
            Tables\Actions\Action::make('download')
                ->label('Pobierz')
                ->url(fn ($record) => route('attachments.download', $record))
                ->openInNewTab(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }
}
