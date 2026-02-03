<?php

namespace App\Filament\Resources\ContractResource\RelationManagers;

use App\Models\Contract;
use App\Services\Finance\InstallmentAutoGenerator;
use App\Services\Finance\InstallmentTaskSyncService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class InstallmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'installments';

    protected static ?string $title = 'Harmonogram wpłat';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DatePicker::make('due_date')
                    ->label('Termin')
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('Kwota')
                    ->numeric()
                    ->required()
                    ->suffix('PLN'),
                Forms\Components\Toggle::make('is_paid')
                    ->label('Opłacona')
                    ->inline(false)
                    ->live(),
                Forms\Components\DatePicker::make('paid_at')
                    ->label('Data opłacenia')
                    ->visible(fn (Forms\Get $get) => (bool) $get('is_paid')),
                Forms\Components\TextInput::make('note')
                    ->label('Notatka')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('due_date')
            ->columns([
                Tables\Columns\TextColumn::make('due_date')
                    ->label('Termin')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Kwota')
                    ->money('PLN')
                    ->sortable()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Suma')),
                Tables\Columns\TextColumn::make('is_paid')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Opłacona' : 'Do zapłaty')
                    ->color(fn ($state) => $state ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('paid_at')
                    ->label('Opłacono')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('note')
                    ->label('Notatka')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                Tables\Actions\Action::make('autoGenerate')
                    ->label('Auto-utwórz raty')
                    ->icon('heroicon-o-bolt')
                    ->color('gray')
                    ->form([
                        Forms\Components\Toggle::make('replace_existing')
                            ->label('Usuń istniejące raty i utwórz od nowa')
                            ->default(false)
                            ->inline(false),
                        Forms\Components\TextInput::make('deposit_percent')
                            ->label('Zaliczka (%)')
                            ->numeric()
                            ->default(30)
                            ->required(),
                        Forms\Components\DatePicker::make('deposit_due_date')
                            ->label('Termin zaliczki')
                            ->default(now())
                            ->required(),
                        Forms\Components\TextInput::make('final_due_days_before_start')
                            ->label('Ile dni przed startem termin dopłaty')
                            ->numeric()
                            ->default(14)
                            ->required(),
                        Forms\Components\Toggle::make('sync_tasks')
                            ->label('Od razu odśwież zadania rat')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->action(function (array $data, InstallmentAutoGenerator $generator, InstallmentTaskSyncService $sync) {
                        /** @var Contract $contract */
                        $contract = $this->getOwnerRecord();

                        $created = $generator->generate($contract, $data);

                        if ($created <= 0) {
                            Notification::make()
                                ->title('Nie można utworzyć rat')
                                ->body('Umowa nie ma total_amount ani policzalnej kwoty z uczestników.')
                                ->danger()
                                ->send();
                            return;
                        }

                        if (!empty($data['sync_tasks'])) {
                            $sync->sync([
                                'days_ahead' => 14,
                                'author_id' => auth()->id() ?? 1,
                            ]);
                        }

                        Notification::make()
                            ->title('Utworzono raty: ' . $created)
                            ->success()
                            ->send();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('due_date', 'asc');
    }
}
