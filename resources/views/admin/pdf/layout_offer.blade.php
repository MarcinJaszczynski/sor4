<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { 
            font-family: 'DejaVu Sans', sans-serif; 
            font-size: 12px; 
            line-height: 1.4; 
            color: #333;
        }
        h1, h2, h3, h4, h5, h6 { color: #1a56db; margin: 5px 0; }
        h1 { font-size: 24px; text-transform: uppercase; border-bottom: 2px solid #1a56db; padding-bottom: 10px; margin-bottom: 20px;}
        h2 { font-size: 18px; margin-bottom: 15px; color: #444; }
        h3 { font-size: 14px; background-color: #f3f4f6; padding: 5px 10px; border-left: 4px solid #1a56db; margin-top: 20px; }
        
        .header-table { width: 100%; margin-bottom: 20px; }
        .header-table td { vertical-align: top; }
        .logo { font-size: 20px; font-weight: bold; color: #1a56db; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 5px; border-bottom: 1px solid #eee; }
        .label { font-weight: bold; width: 120px; color: #666; }
        
        .content-block { margin-bottom: 15px; text-align: justify; }
        
        .program-table { width: 100%; border-collapse: collapsed; margin-bottom: 15px; }
        .program-table th { background: #1a56db; color: white; padding: 8px; text-align: left; font-size: 11px; }
        .program-table td { border-bottom: 1px solid #ddd; padding: 8px; font-size: 11px; }
        
        .price-box { border: 2px solid #1a56db; padding: 10px; text-align: center; margin: 20px 0; background: #eff6ff; }
        .price-value { font-size: 18px; font-weight: bold; color: #1a56db; }
        
        .footer { position: fixed; bottom: 0; left: 0; right: 0; font-size: 10px; color: #999; text-align: center; border-top: 1px solid #eee; padding-top: 10px; }
        
        .day-header { font-weight: bold; color: #1a56db; margin-top: 10px; padding-bottom: 3px; border-bottom: 1px dotted #ccc; }
        .point-item { margin-left: 10px; margin-bottom: 5px; }
        .point-time { font-weight: bold; color: #555; display: inline-block; width: 50px; }
    </style>
</head>
<body>
    <div class="footer">
        Oferta wygenerowana systemowo: {{ date('d.m.Y H:i') }} | BP RAFA
    </div>

    <table class="header-table">
        <tr>
            <td width="60%">
                <div class="logo">BP RAFA</div>
                <div>Biuro Podróży</div>
                <div style="font-size: 10px; color: #777;">ul. Przykładowa 123, 00-000 Miasto</div>
            </td>
            <td width="40%" style="text-align: right;">
                <h1 style="border:0; margin:0; padding:0; text-align:right;">OFERTA</h1>
                <div style="color: #ed8936; font-weight: bold; font-size: 14px;">@yield('offer_type', 'Standardowa')</div>
            </td>
        </tr>
    </table>

    <h2>{{ $offer->name }}</h2>

    <table class="info-table">
        <tr>
            <td class="label">Impreza:</td>
            <td>{{ $offer->event->name ?? '—' }}</td>
            <td class="label">Termin:</td>
            <td>
                @if($offer->event && $offer->event->start_date)
                    {{ $offer->event->start_date }} 
                    @if($offer->event->end_date) - {{ $offer->event->end_date }} @endif
                @else
                    Do ustalenia
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Uczestnicy:</td>
            <td>{{ $offer->participant_count ?? ($offer->event->participant_count ?? '—') }} os.</td>
            <td class="label">Ważność:</td>
            <td>{{ $offer->valid_until?->format('d.m.Y') ?? 'Bezterminowo' }}</td>
        </tr>
    </table>

    <div class="content-block">
        <h3>Wstęp</h3>
        <div>{!! $offer->introduction !!}</div>
    </div>

    <div class="content-block">
        <h3>Program</h3>
        @yield('program_content')
    </div>

    <div class="content-block">
        <h3>Świadczenia i koszty</h3>
        
        <table class="program-table" style="width: 100%; margin-top: 10px; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f3f4f6;">
                    <th style="padding: 8px; border-bottom: 2px solid #ddd; text-align: left;">Wariant / Liczba uczestników</th>
                    <th style="padding: 8px; border-bottom: 2px solid #ddd; text-align: right;">Cena za osobę</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($priceTable) && count($priceTable) > 0)
                    @foreach($priceTable as $row)
                        <tr style="{{ $row['is_current'] ? 'background-color: #eff6ff;' : '' }}">
                            <td style="padding: 8px; border-bottom: 1px solid #ddd; {{ $row['is_current'] ? 'font-weight: bold;' : '' }}">
                                @if($row['is_current'])
                                    {{ $row['qty'] }} osób (bieżąca)
                                @else
                                    {{ $row['qty'] }} osób
                                @endif
                            </td>
                            <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right; {{ $row['is_current'] ? 'font-weight: bold; color: #1a56db;' : '' }}">
                                {{ number_format($row['price'], 2, ',', ' ') }} {{ $row['curr'] }}
                            </td>
                        </tr>
                    @endforeach
                @else
                    {{-- Minimal fallback if logic fails or empty --}}
                    @if($offer->price_per_person > 0)
                    <tr style="background-color: #eff6ff;">
                        <td style="padding: 8px; border-bottom: 1px solid #ddd; font-weight: bold;">
                            {{ $offer->participant_count }} osób (bieżąca)
                        </td>
                        <td style="padding: 8px; border-bottom: 1px solid #ddd; text-align: right; font-weight: bold; color: #1a56db;">
                            {{ number_format($offer->price_per_person, 2, ',', ' ') }} PLN
                        </td>
                    </tr>
                    @endif
                @endif
            </tbody>
        </table>
    </div>

    <div class="content-block">
        <h3>Warunki i informacje dodatkowe</h3>
        <div style="font-size: 11px;">{!! $offer->summary !!}</div>
        <div style="font-size: 10px; color: #555; margin-top: 10px;">{!! $offer->terms !!}</div>
    </div>

</body>
</html>
