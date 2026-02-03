@extends('admin.pdf.layout_offer')

@section('offer_type', 'Oferta Zagraniczna')

@section('program_content')
    <div style="margin-bottom: 20px;">
        @if(isset($program) && count($program) > 0)
            @foreach($program as $day => $points)
                <div class="day-section" style="margin-top: 15px; page-break-inside: avoid;">
                    <div class="day-header" style="background: #fff; color: #d97706; padding: 5px; font-weight: bold; border-bottom: 2px solid #d97706; text-transform: uppercase;">
                        Dzień {{ $day }}
                    </div>
                    
                    <ul class="point-list" style="list-style-type: none; padding: 0; margin-top: 5px;">
                    @foreach($points as $point)
                         <li class="point-item" style="margin-bottom: 8px; margin-left: 10px; padding-bottom: 5px;">
                            {{-- Title --}}
                            <div style="{{ $point->show_title_style ? 'font-weight: bold;' : '' }}">
                                {{ preg_replace('/\s*-?\s*\d+:\d+h?.*$/', '', strip_tags($point->name)) }}
                            </div>

                            {{-- Description --}}
                            @if($point->show_description && $point->description)
                                <div style="font-size: 11px; color: #444; margin-top: 2px;">
                                    {!! strip_tags($point->description) !!}
                                </div>
                            @endif

                             {{-- Children --}}
                            @if(isset($point->children) && count($point->children) > 0)
                                <ul style="list-style-type: square; margin-left: 15px; padding-left: 0; margin-top: 3px; color: #d97706;">
                                    @foreach($point->children as $child)
                                        @if($child->include_in_program)
                                            <li style="margin-bottom: 3px; font-size: 11px; color: #333;">
                                                <span style="{{ $child->show_title_style ? 'font-weight: bold;' : '' }}">
                                                    {{ preg_replace('/\s*-?\s*\d+:\d+h?.*$/', '', strip_tags($child->name)) }}
                                                </span>
                                                @if($child->show_description && $child->description)
                                                     – {!! strip_tags($child->description) !!}
                                                @endif
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endforeach
                    </ul>
                </div>
            @endforeach
        @else
            <p>Brak programu.</p>
        @endif

        <div style="margin-top: 30px; border: 1px solid #d97706; padding: 10px; background-color: #fffbeb;">
            <strong style="color: #d97706;">Ważne informacje dla wyjazdów zagranicznych:</strong>
            <p style="font-size: 10px; margin: 5px 0;">Prosimy o sprawdzenie ważności dokumentów podróży (Dowód Osobisty / Paszport). Pamiętaj o karcie EKUZ oraz ubezpieczeniu.</p>
        </div>
    </div>
@endsection
