<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Filament\Resources\DocumentResource\RelationManagers;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BooleanColumn;
use Illuminate\Support\Facades\Auth;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Dokumenty';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)
                ->schema([
                    Forms\Components\Group::make([
                        Forms\Components\Section::make('Dokument')
                            ->schema([
                                Forms\Components\Select::make('document_section_id')
                                    ->label('Sekcja')
                                    ->relationship('section', 'title')
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(4),
                                
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(3),
                                Forms\Components\TextInput::make('order_number')
                                    ->label('Kolejność')
                                    ->numeric()
                                    ->default(0)
                                    ->columnSpan(1),
                                Forms\Components\Toggle::make('is_published')
                                    ->label('Opublikowany')
                                    ->default(true)
                                    ->inline(false)
                                    ->columnSpan(2),
                                    
                                Forms\Components\Textarea::make('excerpt')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Forms\Components\RichEditor::make('content')
                                    ->label('Treść')
                                    ->columnSpanFull(),
                            ])->columns(6)->compact(),
                    ])->columnSpan(2),

                    Forms\Components\Group::make([
                        Forms\Components\Section::make('Załączniki')
                            ->schema([
                                Forms\Components\Repeater::make('attachments')
                                    ->relationship()
                                    ->schema([
                                        Forms\Components\FileUpload::make('path')
                                            ->label('Plik')
                                            ->disk('public')
                                            ->directory('document-attachments')
                                            ->preserveFilenames()
                                            ->storeFileNamesIn('original_name')
                                            ->openable()
                                            ->downloadable(),
                                    ])
                                    ->defaultItems(0)
                                    ->addActionLabel('Dodaj plik')
                                    ->deleteAction(
                                        fn (Forms\Components\Actions\Action $action) => $action->requiresConfirmation(),
                                    )
                                    ->collapsible()
                                    ->collapsed(fn ($state) => count($state ?? []) > 0),
                            ])->compact(),
                    ])->columnSpan(1),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Tytuł')->searchable(),
                TextColumn::make('section.title')->label('Sekcja'),
                BooleanColumn::make('is_published')->label('Opublikowany'),
                TextColumn::make('order_number')->label('Kolejność')->sortable(),
            ])
            ->defaultSort('order_number');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        if (!$user || !$user instanceof \App\Models\User) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        return $user->can('create document');
    }
}
