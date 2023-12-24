<div>
    <div class="p-9">
        <p class="mb-5 text-neutral-200 text-sm/4">
            {{ $num === 1 ? __('lychee.ALBUM_NEW_TITLE') : sprintf(__('lychee.ALBUMS_NEW_TITLE'), $num) }}
        </p>
        <form>
            <div class="my-3 first:mt-0 last:mb-0">
                <x-forms.inputs.text class="w-full" autocapitalize="off" wire:model="title" x-intersect="$el.focus()" 
                    placeholder="{{ __('lychee.UNTITLED') }}" :has_error="$errors->has('title')" />
            </div>
        </form>
    </div>
    <div class="flex w-full box-border">
        <x-forms.buttons.cancel class="border-t border-t-bg-800 rounded-bl-md w-full"
            @keydown.escape.window="$wire.close()"
            wire:click="close">{{ __('lychee.CANCEL') }}</x-forms.buttons.cancel>
        <x-forms.buttons.action class="border-t border-t-bg-800 rounded-br-md w-full"
            @keydown.enter.window="$wire.submit()"
            wire:click="submit">{{ __('lychee.ALBUM_SET_TITLE') }}</x-forms.buttons.action>
    </div>
</div>
