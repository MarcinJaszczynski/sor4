@php
    $offerAssistantEndpoint = route('offer.assistant');
    $documentsUrl = route('documents.global');
@endphp

<div id="offer-assistant-root" style="position: fixed; right: 12px; bottom: 120px; z-index: 15000; max-width: calc(100vw - 24px);">
    <button id="offer-assistant-toggle" type="button" class="btn btn-primary" style="border-radius: 24px;">
        Asystent oferty
    </button>

    <div id="offer-assistant-panel" class="card" style="display:none; width: 360px; max-width: calc(100vw - 24px); margin-top: 8px;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Asystent oferty</strong>
            <button id="offer-assistant-close" type="button" class="btn btn-sm btn-outline-secondary">Zamknij</button>
        </div>
        <div class="card-body" style="max-height: 45vh; overflow: auto;">
            <div id="offer-assistant-messages" style="display:flex; flex-direction:column; gap: 8px;"></div>
            <div class="small text-muted" style="margin-top: 10px;">
                Odpowiedzi są oparte wyłącznie o publiczne treści serwisu.
                <a href="{{ $documentsUrl }}" target="_blank" rel="noopener">Dokumenty / warunki</a>
            </div>
        </div>
        <div class="card-footer">
            <form id="offer-assistant-form" class="d-flex" style="gap: 8px;">
                <input id="offer-assistant-q" type="text" class="form-control" placeholder="Np. czy jest posiłek?" maxlength="250" />
                <button id="offer-assistant-send" type="submit" class="btn btn-primary">Wyślij</button>
            </form>
        </div>
    </div>
</div>

<script>
    (function(){
        var endpoint = @json($offerAssistantEndpoint);
        var toggleBtn = document.getElementById('offer-assistant-toggle');
        var closeBtn = document.getElementById('offer-assistant-close');
        var panel = document.getElementById('offer-assistant-panel');
        var form = document.getElementById('offer-assistant-form');
        var input = document.getElementById('offer-assistant-q');
        var sendBtn = document.getElementById('offer-assistant-send');
        var messagesEl = document.getElementById('offer-assistant-messages');

        if (!toggleBtn || !panel || !form || !input || !sendBtn || !messagesEl) return;

        function getContext(){
            var ctx = (window && window.__offerAssistantContext) ? window.__offerAssistantContext : {};
            return {
                eventTemplateId: (ctx && typeof ctx.eventTemplateId === 'number') ? ctx.eventTemplateId : null,
                regionSlug: (ctx && typeof ctx.regionSlug === 'string') ? ctx.regionSlug : null
            };
        }

        function setOpen(open){
            panel.style.display = open ? 'block' : 'none';
        }

        function appendMessage(role, text){
            var row = document.createElement('div');
            row.className = role === 'user' ? 'text-right' : '';
            var bubble = document.createElement('div');
            bubble.className = role === 'user' ? 'alert alert-primary' : 'alert alert-secondary';
            bubble.style.marginBottom = '0';
            bubble.style.whiteSpace = 'pre-wrap';
            bubble.textContent = text;
            row.appendChild(bubble);
            messagesEl.appendChild(row);
            try { panel.querySelector('.card-body').scrollTop = panel.querySelector('.card-body').scrollHeight; } catch (e) {}
        }

        function setLoading(isLoading){
            sendBtn.disabled = isLoading;
            sendBtn.textContent = isLoading ? '…' : 'Wyślij';
        }

        toggleBtn.addEventListener('click', function(){
            var isOpen = panel.style.display !== 'none';
            setOpen(!isOpen);
            if (!isOpen && messagesEl.children.length === 0) {
                appendMessage('assistant', 'Zadaj pytanie o ofertę (posiłki, transport, program, warunki).');
            }
        });
        if (closeBtn) closeBtn.addEventListener('click', function(){ setOpen(false); });

        form.addEventListener('submit', function(e){
            e.preventDefault();
            var q = (input.value || '').trim();
            if (!q) return;

            var ctx = getContext();
            appendMessage('user', q);
            input.value = '';

            setLoading(true);

            var url = endpoint + '?q=' + encodeURIComponent(q);
            if (ctx.eventTemplateId) url += '&event_template_id=' + encodeURIComponent(ctx.eventTemplateId);
            if (ctx.regionSlug) url += '&regionSlug=' + encodeURIComponent(ctx.regionSlug);
            url += '&verbose=1';

            fetch(url, { method: 'GET', headers: { 'Accept': 'application/json' } })
                .then(function(r){ return r.json(); })
                .then(function(data){
                    var ans = (data && data.answer) ? data.answer : 'Brak odpowiedzi.';
                    appendMessage('assistant', ans);
                })
                .catch(function(){
                    appendMessage('assistant', 'Wystąpił błąd. Spróbuj ponownie.');
                })
                .finally(function(){
                    setLoading(false);
                });
        });
    })();
</script>
