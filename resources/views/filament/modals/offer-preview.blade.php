<div style="max-height:70vh; overflow:auto; padding:1rem; font-family: DejaVu Sans, sans-serif;">
    <h2>{{ $offer->name }}</h2>
    <p><strong>Impreza:</strong> {{ $offer->event?->name ?? '—' }}</p>
    <p><strong>Ważna do:</strong> {{ $offer->valid_until?->format('d.m.Y') ?? '—' }}</p>

    <hr>
    <h3>Wstęp</h3>
    <div>{!! $offer->introduction !!}</div>

    <h3>Podsumowanie</h3>
    <div>{!! $offer->summary !!}</div>

    <h3>Warunki</h3>
    <div>{!! $offer->terms !!}</div>
</div>
