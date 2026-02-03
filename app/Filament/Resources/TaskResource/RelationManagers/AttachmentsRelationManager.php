<?php

namespace App\Filament\Resources\TaskResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class AttachmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'attachments';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\FileUpload::make('file_path')
                ->label('Plik')
                ->directory('task-attachments')
                ->disk('public')
                ->storeFileNamesIn('name')
                ->preserveFilenames()
                ->acceptedFileTypes(['image/*', 'application/pdf', 'application/zip'])
                ->maxSize(10240) // KB (10 MB)
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('file_path')
                    ->disk('public')
                    ->label('Podgląd')
                    ->rounded(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nazwa')
                    ->formatStateUsing(fn ($state, $record) => $state ?: basename($record->file_path)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dodano')
                    ->dateTime(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = \Illuminate\Support\Facades\Auth::id();
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Pobierz')
                    ->icon('heroicon-o-download')
                    ->url(fn ($record) => Storage::url($record->file_path))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}