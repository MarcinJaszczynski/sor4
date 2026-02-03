<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DocumentsRelationManager extends RelationManager
{
    protected static string $relationship = 'documents';
    protected static ?string $title = 'Dokumenty';
    protected static ?string $icon = 'heroicon-o-document';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Szczegóły dokumentu')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nazwa dokumentu')
                            ->required()
                            ->maxLength(255),
                        
                        Forms\Components\Group::make([
                            Forms\Components\Toggle::make('is_visible_office')
                                ->label('Biuro')
                                ->default(true),
                            Forms\Components\Toggle::make('is_visible_driver')
                                ->label('Kierowca'),
                            Forms\Components\Toggle::make('is_visible_hotel')
                                ->label('Hotel'),
                            Forms\Components\Toggle::make('is_visible_pilot')
                                ->label('Pilot'),
                            Forms\Components\Toggle::make('is_visible_client')
                                ->label('Klient'),
                        ])->columns(2)->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->compact(),
                
                Forms\Components\Section::make('Plik')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('Plik')
                            ->disk('public')
                            ->directory('event-documents')
                            ->required()
                            ->openable()
                            ->downloadable(),
                    ])
                    ->compact(),
                
                Forms\Components\Section::make('Dodatkowe informacje')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Opis')
                            ->rows(3),
                    ])
                    ->collapsed()
                    ->compact(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->paginationPageOptions([15])
            ->defaultPaginationPageOption(15)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nazwa')
                    ->searchable()
                    ->description(fn ($record) => $record->description 
                        ? mb_strimwidth($record->description, 0, 60, '...') 
                        : null
                    ),
                
                Tables\Columns\IconColumn::make('is_visible_office')
                    ->label('Biuro')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_visible_driver')
                    ->label('Kierowca')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_visible_hotel')
                    ->label('Hotel')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_visible_pilot')
                    ->label('Pilot')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_visible_client')
                    ->label('Klient')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dodano')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->hiddenLabel()
                    ->tooltip('Dodaj dokument'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->hiddenLabel(),
                Tables\Actions\DeleteAction::make()
                    ->hiddenLabel(),
                Tables\Actions\Action::make('download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->hiddenLabel()
                    ->tooltip('Pobierz')
                    ->url(fn ($record) => \Illuminate\Support\Facades\Storage::url($record->file_path))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
