<?php

namespace App\Http\Controllers\Admin;

use App\Filament\Resources\CalendarNoteResource;
use App\Filament\Resources\EventResource;
use App\Models\CalendarNote;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class CalendarFeedController
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json([], 401);
        }

        $start = $request->query('start');
        $end = $request->query('end');

        try {
            $startDt = $start ? Carbon::parse($start)->startOfDay() : now()->startOfMonth();
            $endDt = $end ? Carbon::parse($end)->endOfDay() : now()->endOfMonth();
        } catch (\Throwable $_) {
            $startDt = now()->startOfMonth();
            $endDt = now()->endOfMonth();
        }

        $isManager = method_exists($user, 'hasRole')
            ? $user->hasRole(['super_admin', 'admin', 'manager'])
            : false;

        $eventsQuery = Event::query()
            ->select(['id', 'name', 'start_date', 'end_date', 'assigned_to', 'created_by'])
            ->whereNotNull('start_date')
            ->whereBetween('start_date', [$startDt, $endDt]);

        if (! $isManager) {
            $eventsQuery->where(function ($q) use ($user) {
                $q->where('assigned_to', $user->id)
                    ->orWhere('created_by', $user->id);
            });
        }

        $events = $eventsQuery->get()->map(function (Event $event) {
            $start = $event->start_date?->toDateString();
            $end = $event->end_date?->toDateString();

            // FullCalendar expects exclusive end for all-day spans.
            $endExclusive = null;
            if ($end && $start) {
                $endExclusive = Carbon::parse($end)->addDay()->toDateString();
            }

            return [
                'id' => 'event:' . $event->id,
                'title' => $event->name,
                'start' => $start,
                'end' => $endExclusive,
                'allDay' => true,
                'url' => EventResource::getUrl('edit', ['record' => $event->id]),
                'backgroundColor' => '#0ea5a4',
                'borderColor' => '#0ea5a4',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'event',
                    'recordId' => $event->id,
                ],
            ];
        });

        $notesQuery = CalendarNote::query()
            ->select(['id', 'user_id', 'date', 'title'])
            ->whereBetween('date', [$startDt->toDateString(), $endDt->toDateString()]);

        if (! $isManager) {
            $notesQuery->where('user_id', $user->id);
        }

        $notes = $notesQuery->get()->map(function (CalendarNote $note) {
            return [
                'id' => 'note:' . $note->id,
                'title' => $note->title ?: 'Notatka',
                'start' => $note->date,
                'allDay' => true,
                'url' => CalendarNoteResource::getUrl('edit', ['record' => $note->id]),
                'backgroundColor' => '#6366f1',
                'borderColor' => '#6366f1',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'type' => 'note',
                    'recordId' => $note->id,
                ],
            ];
        });

        return response()->json($events->concat($notes)->values());
    }
}
