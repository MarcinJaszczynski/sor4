<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogPostResource\Pages;
use App\Models\BlogPost;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Blog';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Artykuł')
                ->schema([
                    Forms\Components\TextInput::make('title')
                        ->required()
                        ->reactive()
                        ->columnSpan(3)
                        ->afterStateUpdated(function ($state, $set) {
                            if ($state) {
                                $set('slug', Str::slug($state));
                            }
                        }),

                    Forms\Components\TextInput::make('slug')
                        ->required()
                        ->unique(ignorable: fn($record) => $record)
                        ->columnSpan(3),
                        
                    Forms\Components\Textarea::make('excerpt')
                        ->label('Krótki opis')
                        ->maxLength(500)
                        ->columnSpan(4), // Give it more width
                        
                    Forms\Components\DateTimePicker::make('published_at')
                        ->label('Data publikacji')
                        ->columnSpan(2),

                    Forms\Components\Select::make('tags')
                        ->label('Tagi')
                        ->relationship('tags', 'name')
                        ->multiple()
                        ->columnSpan(4),

                    Forms\Components\Toggle::make('is_featured')
                        ->label('Polecany')
                        ->inline(false)
                        ->columnSpan(1),
                    Forms\Components\Toggle::make('is_published')
                        ->label('Opublikowany')
                        ->inline(false)
                        ->columnSpan(1),

                    Forms\Components\FileUpload::make('featured_image')
                        ->image()
                        ->label('Obraz wyróżniający')
                        ->disk('public')
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                        ->columnSpan(3)
                        ->getUploadedFileUsing(function ($file, $storedFileNames): ?array {
                            if (blank($file)) {
                                return null;
                            }
                            try {
                                $disk = Storage::disk('public');
                                $size = $disk->exists($file) ? $disk->size($file) : 0;
                                $type = $disk->exists($file) ? $disk->mimeType($file) : null;
                            } catch (\Throwable $e) {
                                $size = 0; $type = null;
                            }
                            return [
                                'name' => basename($file),
                                'size' => $size,
                                'type' => $type,
                                'url' => '/storage/' . ltrim($file, '/'),
                            ];
                        })
                        ->nullable(),

                    Forms\Components\FileUpload::make('gallery')
                        ->label('Galeria')
                        ->image()
                        ->multiple()
                        ->disk('public')
                        ->directory('blog/gallery')
                        ->preserveFilenames()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
                        ->columnSpan(3)
                        ->getUploadedFileUsing(function ($file, $storedFileNames): ?array {
                            if (blank($file)) { return null; }
                            try {
                                $disk = Storage::disk('public');
                                $size = $disk->exists($file) ? $disk->size($file) : 0;
                                $type = $disk->exists($file) ? $disk->mimeType($file) : null;
                            } catch (\Throwable $e) { $size = 0; $type = null; }
                            return [
                                'name' => basename($file),
                                'size' => $size,
                                'type' => $type,
                                'url' => '/storage/' . ltrim($file, '/'),
                            ];
                        })
                        ->nullable(),

                    Forms\Components\RichEditor::make('content')
                        ->label('Treść')
                        ->required()
                        ->columnSpanFull(),
                ])->columns(6)->compact(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->limit(50),
                TextColumn::make('published_at')->date()->sortable(),
                IconColumn::make('is_published')->boolean()->label('Opublikowany'),
            ])
            ->defaultSort('published_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBlogPosts::route('/'),
            'create' => Pages\CreateBlogPost::route('/create'),
            'edit' => Pages\EditBlogPost::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        $user = \App\Models\User::query()->find(\Illuminate\Support\Facades\Auth::id());
        if ($user && $user->roles && $user->roles->contains('name', 'admin')) {
            return true;
        }
        if ($user && $user->roles && $user->roles->flatMap->permissions->contains('name', 'create blog post')) {
            return true;
        }
        return false;
    }
}
