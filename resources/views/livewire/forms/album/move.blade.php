<div>
    @if ($title !== null && $title !== '')
        <div class="p-9">
            <p class="mb-4 text-center">
                @if ($num === 1)
                    {{ sprintf(__('lychee.ALBUM_MOVE'), $titleMoved, $title) }}
                @else
                    {{ sprintf(__('lychee.ALBUMS_MOVE'), $title) }}
                @endif
            </p>
        </div>
        <div class="flex w-full box-border">
            <x-forms.buttons.cancel class="border-t border-t-dark-800 rounded-bl-md w-full" wire:click="close">
                {{ __('lychee.NOT_MOVE_ALBUMS') }}
            </x-forms.buttons.cancel>
            <x-forms.buttons.action class="rounded-md w-full" @keydown.enter.window="$wire.submit()" wire:click='submit'>
                {{ $num === 1 ? __('lychee.MOVE_ALBUM') : __('lychee.MOVE_ALBUMS') }}
            </x-forms.buttons.action>
        </div>
    @else
        <div class="p-9">
            <div class="w-full">
                <span class="font-bold">
                    {{ $num === 1 ? __('lychee.MOVE_ALBUM') : __('lychee.MOVE_ALBUMS') }} to
                </span>
            </div>
            <livewire:forms.album.search-album lazy :parent_id="$parent_id" :lft="$lft" :rgt="$rgt" />
        </div>
        <div class="flex w-full box-border">
            <x-forms.buttons.cancel class="border-t border-t-dark-800 rounded-bl-md w-full" wire:click="close">
                {{ __('lychee.CANCEL') }}
            </x-forms.buttons.cancel>
        </div>
    @endif
</div>
