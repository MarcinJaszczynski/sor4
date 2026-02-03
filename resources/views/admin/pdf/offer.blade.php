@extends('admin.pdf.layout_offer')

@section('program_content')
    @if(isset($program) && count($program) > 0)
        @foreach($program as $day => $points)
            <div class="program-day" style="margin-bottom: 15px;">
                <h4 style="color: #444; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-top: 0;">Dzień {{ $day }}</h4>
                <ul style="list-style-type: none; padding-left: 0;">
                    @foreach($points as $point)
                        <li style="margin-bottom: 10px;">
                            <strong style="color: #000;">{{ $point->name }}</strong>
                            <div style="font-size: 11px; color: #555; margin-top: 2px;">
                                {!! $point->description !!}
                            </div>
                            @if(isset($point->children) && count($point->children) > 0)
                                <ul style="list-style-type: circle; padding-left: 20px; margin-top: 5px; color: #666; font-size: 11px;">
                                    @foreach($point->children as $child)
                                        <li>{{ $child->name }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    @else
        <p style="color: #777; font-style: italic;">Brak szczegółowego programu.</p>
    @endif
@endsection
