@if(isset($program) && count($program) > 0)
    <div style="margin-bottom: 20px;">
        @foreach($program as $day => $points)
            <div class="day-section" style="margin-top: 15px; page-break-inside: avoid;">
                <div class="day-header" style="background: #eef2ff; color: #1a56db; padding: 5px; font-weight: bold; border-left: 4px solid #1a56db;">
                    DZIEŃ {{ $day }}
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
                            <div style="font-size: 11px; color: #444; margin-top: 2px; text-align: justify;">
                                {!! strip_tags($point->description) !!}
                            </div>
                        @endif

                        {{-- Children --}}
                        @if($point->children && $point->children->count() > 0)
                            <ul style="list-style-type: circle; margin-left: 15px; padding-left: 0; margin-top: 3px; color: #555;">
                                @foreach($point->children as $child)
                                    @if($child->include_in_program)
                                        <li style="margin-bottom: 3px; font-size: 11px;">
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
    </div>
@else
    <p style="color: #666; font-style: italic;">Brak szczegółowego programu.</p>
@endif
