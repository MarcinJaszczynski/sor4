<div class="flex flex-col gap-1 min-w-[300px] text-wrap whitespace-normal">
    {{-- NAME --}}
    <div class="font-bold text-sm text-black">
        @if($point->parent_id) <span class="text-black">↳</span> @endif
        {{ $point->templatePoint?->name ?? $point->name ?? '—' }}
    </div>

    {{-- DESCRIPTION (Zestaw / Notes) --}}
    @php
        $description = null;
        if ($point->parent_id && $point->parent) {
            $parentName = $point->parent->templatePoint?->name ?? $point->parent->name ?? '—';
            $description = 'Zestaw: ' . $parentName;
        } elseif (($point->children_count ?? 0) > 0) {
            $description = 'Zestaw';
        } else {
             $description = $point->notes ? strip_tags($point->notes) : null;
        }
    @endphp

    @if($description)
        <div class="text-xs text-black pb-2">
            {{ $description }}
        </div>
    @endif

    {{-- RESERVATION & CONTRACTOR CONTROLS --}}
    <div class="mt-1">
        @include('filament.resources.event-resource.relation-managers.program-point-row-details', ['point' => $point, 'manager' => $manager, 'contractors' => $contractors])
    </div>
</div>
