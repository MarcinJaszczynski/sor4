<div x-data="{ body: {{ json_encode($getState()) }} }" class="w-full bg-white rounded-lg p-2 min-h-[600px] border border-gray-300">
    <iframe 
        :srcdoc="body" 
        style="width: 100%; height: 600px; border: none;"
        sandbox="allow-popups allow-same-origin"
    ></iframe>
</div>
