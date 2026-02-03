<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfferTemplateResource\Pages;
use App\Filament\Resources\OfferTemplateResource\RelationManagers;
use App\Models\OfferTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OfferTemplateResource extends Resource
{
    protected static ?string $model = OfferTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $navigationGroup = 'Ustawienia';
    protected static ?int $navigationSort = 60;
    protected static ?string $navigationLabel = 'Szablony ofert';
    protected static ?string $modelLabel = 'Szablon oferty';
    protected static ?string $pluralModelLabel = 'Szablony ofert';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Dane podstawowe')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nazwa szablonu')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(3),
                        Forms\Components\Select::make('view_name')
                            ->label('Plik widoku')
                            ->options([
                                'admin.pdf.offer' => 'Standardowy',
                                'admin.pdf.template_one_day' => 'Oferta Jednodniowa',
                                'admin.pdf.template_multi_day' => 'Oferta Wielodniowa',
                                'admin.pdf.template_foreign' => 'Oferta Zagraniczna',
                                'admin.pdf.template_mixed' => 'Oferta PL + Zagranica',
                            ])
                            ->default('admin.pdf.offer')
                            ->columnSpan(3),
                        Forms\Components\Toggle::make('is_default')
                            ->label('Domyślny szablon')
                            ->helperText('Używany automatycznie przy tworzeniu nowej oferty')
                            ->inline(false)
                            ->columnSpan(2),
                    ])->columns(6)->compact(),
                
                Forms\Components\Section::make('Treść')
                    ->schema([
                        Forms\Components\RichEditor::make('introduction')
                            ->label('Wstęp / Powitanie')
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('terms')
                            ->label('Warunki / Regulamin')
                            ->columnSpanFull(),
                    ])->compact(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nazwa')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_default')
                    ->label('Domyślny')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOfferTemplates::route('/'),
            'create' => Pages\CreateOfferTemplate::route('/create'),
            'edit' => Pages\EditOfferTemplate::route('/{record}/edit'),
        ];
    }
}
