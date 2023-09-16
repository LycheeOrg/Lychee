<div>
    <div class="p-9">
        @if ($title !== null && $title !== '')
            <p class="mb-4 text-center">
                @if (count($albumIDs) === 1)
                    {{ sprintf(__('lychee.ALBUM_MOVE'), $titleMoved, $title) }}
                @else
                    {{ sprintf(__('lychee.ALBUMS_MOVE'), $title) }}
                @endif
            </p>
            <x-forms.buttons.action class="rounded-md w-full"
	            @keydown.enter.window="$wire.submit()"
                wire:click='submit'>{{ count($albumIDs) === 1 ? __('lychee.MOVE_ALBUM') : __('lychee.MOVE_ALBUMS') }}</x-forms.buttons.danger>
            @else
                <div class="w-full">
                    <div class="w-full">
                        <span class="font-bold">{{ 'Move to' }}</span>
                    </div>
                    <livewire:forms.album.search-album lazy :parent_id="$parent_id" :lft="$lft" :rgt="$rgt" />
                </div>
        @endif
    </div>
    <div class="flex w-full box-border">
        <x-forms.buttons.cancel class="border-t border-t-dark-800 rounded-bl-md w-full"
            wire:click="close">{{ __('lychee.CANCEL') }}</x-forms.buttons.cancel>
    </div>
</div>
