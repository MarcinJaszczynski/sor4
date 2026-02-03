@extends('portal.layout')

@section('title', 'Wiadomości')

@section('content')
<div class="space-y-6">
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-4">Wiadomości dotyczące imprezy</h2>
        <p class="text-gray-600 mb-6">Poniżej znajdują się e-maile powiązane z tą wycieczką. Jeśli chcesz wysłać wiadomość do biura, skorzystaj z zakładki Kontakt.</p>

        <div class="space-y-4">
            @forelse($messages as $msg)
            <div class="border rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 flex justify-between items-center cursor-pointer hover:bg-gray-100" onclick="document.getElementById('msg-{{ $msg->id }}').classList.toggle('hidden')">
                    <div>
                        <span class="font-bold text-sm">{{ $msg->from_name ?: $msg->from_address }}</span>
                        <h3 class="font-medium text-lg">{{ $msg->subject }}</h3>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-gray-500 block">{{ $msg->date ? $msg->date->format('d.m.Y H:i') : '' }}</span>
                        <i class="fas fa-chevron-down text-gray-400 mt-1"></i>
                    </div>
                </div>
                <div id="msg-{{ $msg->id }}" class="hidden p-4 bg-white border-t">
                    <div class="prose max-w-none text-sm text-gray-800">
                        {!! $msg->body_html ?: nl2br(e($msg->body_text)) !!}
                    </div>
                    @if($msg->attachments && $msg->attachments->count())
                    <div class="mt-4 border-t pt-3">
                        <h4 class="font-semibold mb-2">Załączniki</h4>
                        <ul class="list-disc pl-5 text-sm">
                            @foreach($msg->attachments as $att)
                                <li class="mb-1">
                                    <a href="{{ route('attachments.download', $att) }}" target="_blank" class="text-blue-600 hover:underline">{{ $att->file_name ?? basename($att->file_path) }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-12 bg-gray-50 rounded-lg">
                <i class="far fa-envelope-open text-4xl text-gray-300 mb-4 block"></i>
                <p class="text-gray-500">Brak wiadomości powiązanych z tą imprezą.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
