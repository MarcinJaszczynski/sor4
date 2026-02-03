<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use App\Models\Media;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Biblioteka mediów';
    protected static ?int $navigationSort = 40;
    protected static ?string $navigationLabel = 'Media';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Szczegóły pliku')
                ->schema([
                    Forms\Components\FileUpload::make('path')
                        ->label('Plik')
                        ->disk('public')
                        ->directory('media_library')
                        ->required()
                        ->columnSpanFull()
                        ->live()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state) {
                                // Attempt to suggest a title from filename
                                // Note: state is the path or array of paths.
                                // Filament handles upload, we just get path.
                            }
                        }),
                    Forms\Components\TextInput::make('title')
                        ->label('Tytuł')
                        ->columnSpan(3),
                    Forms\Components\TextInput::make('alt')
                        ->label('Tekst alternatywny')
                        ->columnSpan(3),
                    Forms\Components\Textarea::make('caption')
                        ->label('Podpis')
                        ->rows(2)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('description')
                        ->label('Opis')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(6)->compact(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 4,
                'xl' => 6,
            ])
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\ImageColumn::make('path')
                        ->label('Podgląd')
                        ->disk(fn($record) => $record->disk ?: 'public')
                        ->height('auto')
                        ->width('100%')
                        ->extraImgAttributes(['class' => 'aspect-square object-cover rounded-lg shadow-sm']),
                    Tables\Columns\TextColumn::make('filename')
                        ->label('Nazwa')
                        ->limit(20)
                        ->searchable()
                        ->weight('bold')
                        ->alignCenter()
                        ->copyable(),
                ])->space(3),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('disk')->options([
                    'public' => 'public',
                ]),
                Tables\Filters\SelectFilter::make('extension')->label('Typ')->options([
                    'jpg' => 'jpg','jpeg' => 'jpeg','png' => 'png','webp' => 'webp','gif' => 'gif',
                ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('open')
                    ->label('Link')
                    ->icon('heroicon-o-link')
                    ->url(fn($record) => $record->url())
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at','desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
