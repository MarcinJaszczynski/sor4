<p
    data-validation-error
    title="{{ trim(strip_tags($slot)) }}"
    x-tooltip="{ content: '{{ trim(strip_tags($slot)) }}', theme: $store.theme }"
    {{
        $attributes->class([
            'fi-fo-field-wrp-error-message text-sm text-danger-600 dark:text-danger-400',
        ])
    }}
>
    {{ $slot }}
</p>
