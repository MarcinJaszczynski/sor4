@extends('admin.pdf.layout_offer')

@section('offer_type', 'Oferta PL + Zagranica')

@section('program_content')
    <div style="margin-bottom: 20px;">
        @if(isset($program) && count($program) > 0)
            @foreach($program as $day => $points)
                <div class="day-section" style="margin-top: 15px; page-break-inside: avoid;">
                    <h4 style="color: #059669; border-bottom: 1px solid #059669; padding-bottom: 2px;">Dzień {{ $day }}</h4>
                    
                    @foreach($points as $point)
                        <div style="margin-bottom: 10px;">
                            <div style="font-weight: bold; color: #333;">
                                {{-- Time removed from header as per web view similarity, or kept if relevant. Keeping name clean. --}}
                                {{ preg_replace('/\s*-?\s*\d+:\d+h?.*$/', '', strip_tags($point->name)) }}
                            </div>
                            @if($point->show_description && $point->description)
                                <div style="font-size: 11px; color: #555; text-align: justify;">
                                    {!! strip_tags($point->description) !!}
                                </div>
                            @endif

                             {{-- Children --}}
                            @if(isset($point->children) && count($point->children) > 0)
                                <ul style="list-style-type: disc; margin-left: 20px; padding-left: 0; margin-top: 3px; color: #059669;">
                                    @foreach($point->children as $child)
                                        @if($child->include_in_program)
                                            <li style="margin-bottom: 3px; font-size: 11px; color: #444;">
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
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif
    </div>
@endsection

