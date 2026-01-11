@props([
    'name',
    'show' => false,
    'maxWidth' => 'md'
])

<div
    x-data="{ show: @js($show) }"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    class="modal-backdrop-custom"
    style="display: none; position: fixed; inset: 0; z-index: 1050; background: rgba(0, 0, 0, 0.5);"
>
    <div
        class="modal d-block"
        tabindex="-1"
        x-on:click.self="show = false"
    >
        <div class="modal-dialog modal-dialog-centered modal-{{ $maxWidth }}">
            <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
