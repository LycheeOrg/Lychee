<div>
    @if ($title !== null && $title !== '')
        <div class="p-9">
            <p class="mb-4 text-center">
                @if ($num === 1)
                    {{ sprintf(__('lychee.ALBUM_MERGE'), $titleMoved, $title) }}
                @else
                    {{ sprintf(__('lychee.ALBUMS_MERGE'), $title) }}
                @endif
            </p>
        </div>
        <div class="flex w-full box-border">
            <x-forms.buttons.cancel class="border-t border-t-bg-800 rounded-bl-md w-full"
                @keydown.escape.window="$wire.close()" wire:click="close">
                {{ __('lychee.DONT_MERGE') }}
            </x-forms.buttons.cancel>
            <x-forms.buttons.action class="rounded-md w-full"
                @keydown.enter.window="$wire.submit()" wire:click='submit'>
                {{ __('lychee.MERGE_ALBUM') }}
            </x-forms.buttons.action>
        </div>
    @else
        <div class="p-9">
            <div class="w-full">
                <span class="font-bold">
                    {{ __('lychee.MERGE_ALBUM') }} into
                </span>
            </div>
            <livewire:forms.album.search-album lazy :parent_id="$parent_id" :lft="$lft" :rgt="$rgt" />
        </div>
        <div class="flex w-full box-border">
            <x-forms.buttons.cancel class="border-t border-t-bg-800 rounded-bl-md w-full" wire:click="close">
                {{ __('lychee.CANCEL') }}
            </x-forms.buttons.cancel>
        </div>
    @endif
</div>
