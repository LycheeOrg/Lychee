<div>
    <div class="p-9">
        <p class="mb-4 text-center">
            <input type="file" class="block w-full
            file:mr-5 file:py-1 file:px-3 file:border-[1px]
            file:border-none
            file:text-xs file:font-medium
            file:bg-bg-200 file:text-text-main-800
            file:cursor-pointer
            file:hover:bg-bg-100
            hover:text-text-main-100
            mb-5 text-xs text-text-main-400 border border-bg-800 rounded-md cursor-pointer bg-bg-400 outline-none"
            wire:model="file"
            accept="application/x-gpx+xml">
            <x-forms.error-message field='file' />
        </p>
    </div>
    <div class="flex w-full box-border">
        <x-forms.buttons.cancel class="border-t border-t-bg-800 rounded-bl-md w-full"
            @keydown.escape.window="$wire.close()" wire:click="close">
            {{ __('lychee.CANCEL') }}
        </x-forms.buttons.cancel>
        <x-forms.buttons.action class="border-t border-t-bg-800 border-l border-l-bg-800 rounded-br-md w-full"
            @keydown.enter.window="$wire.submit()" wire:click='submit'>{{ __('lychee.UPLOAD_TRACK') }}
        </x-forms.buttons.action>
    </div>
</div>
