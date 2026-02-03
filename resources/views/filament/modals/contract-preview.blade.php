<div style="max-height:70vh; overflow:auto; padding:1rem; font-family: DejaVu Sans, sans-serif;">
    <h2>Umowa: {{ $contract->contract_number }}</h2>
    <p><strong>Impreza:</strong> {{ $contract->event?->name ?? '—' }}</p>
    <hr>
    <div>{!! $contract->content !!}</div>
</div>
