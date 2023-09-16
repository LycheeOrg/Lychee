<div>
    <div class="p-9">
        <p class="mb-4 text-center">
            @if ($num === 1)
                {{ sprintf(__('lychee.DELETE_ALBUM_CONFIRMATION'), $title) }}
            @else
                {{ sprintf(__('lychee.DELETE_ALBUMS_CONFIRMATION'), $num) }}
            @endif
            {{-- TODO add confirmation by typing album name? --}}
        </p>
    </div>
    <div class="flex w-full box-border">
        <x-forms.buttons.cancel class="border-t border-t-dark-800 rounded-bl-md w-full" wire:click="close">
            {{ $num === 1 ? __('lychee.KEEP_ALBUM') : __('lychee.KEEP_ALBUMS') }}
        </x-forms.buttons.cancel>
        <x-forms.buttons.danger class="rounded-md w-full" wire:click='delete'>
            {{ $num === 1 ? __('lychee.DELETE_ALBUM_QUESTION') : __('lychee.DELETE_ALBUMS_QUESTION') }}
        </x-forms.buttons.danger>
    </div>
</div>
