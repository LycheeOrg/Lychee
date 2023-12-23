<div>
    <div class="p-9">
        <p>
            @if ($num === 1)
                {{ sprintf('Copy %s to:', $title) }}
            @else
                {{ sprintf('Copy %d photos to:', $num) }}
            @endif
        </p>
        <livewire:forms.album.search-album lazy :parent_id="$parent_id" />
    </div>
    <div class="flex w-full box-border">
        <x-forms.buttons.cancel class="border-t border-t-bg-800 rounded-bl-md w-full"
            wire:click="close">{{ __('lychee.CANCEL') }}</x-forms.buttons.cancel>
    </div>
</div>
