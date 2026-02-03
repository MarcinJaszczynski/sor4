<?php

namespace App\Filament\Resources\CalendarNoteResource\Pages;

use App\Filament\Resources\CalendarNoteResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCalendarNote extends CreateRecord
{
    protected static string $resource = CalendarNoteResource::class;

    public function mount(): void
    {
        parent::mount();

        $date = request()->query('date');
        if ($date) {
            $this->form->fill([
                'date' => $date,
            ]);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }
}
