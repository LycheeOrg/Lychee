<div>
    <div class="p-9">
        <p>
            @if ($num === 1)
                {{ sprintf(__('lychee.PHOTO_DELETE_CONFIRMATION'), $title) }}
            @else
                {{ sprintf(__('lychee.PHOTO_DELETE_ALL'), $num) }}
            @endif
        </p>
    </div>
    <div class="flex w-full box-border">
        <x-forms.buttons.cancel class="border-t border-t-dark-800 rounded-bl-md w-full"
            @keydown.escape.window="$wire.close()" wire:click="close">
            {{ __('lychee.PHOTO_KEEP') }}
        </x-forms.buttons.cancel>
        <x-forms.buttons.danger class="border-t border-t-dark-800 rounded-br-md w-full"
            @keydown.enter.window="$wire.submit()" wire:click="submit">
            {{ $num === 1 ? __('lychee.PHOTO_DELETE') : __('lychee.DELETE_ALL') }}
        </x-forms.buttons.danger>
    </div>
</div>
