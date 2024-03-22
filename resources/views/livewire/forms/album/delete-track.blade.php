<div>
    <div class="p-9">
        <p class="mb-4 text-center">
            {{ __('lychee.DELETE_TRACK' )}}
        </p>
    </div>
    <div class="flex w-full box-border">
        <x-forms.buttons.cancel class="border-t border-t-bg-800 rounded-bl-md w-full"
            @keydown.escape.window="$wire.close()" wire:click="close">
            {{ __('lychee.CANCEL') }}
        </x-forms.buttons.cancel>
        <x-forms.buttons.action class="border-t border-t-bg-800 border-l border-l-bg-800 rounded-br-md w-full"
            @keydown.enter.window="$wire.submit()" wire:click='submit'>{{ __('lychee.CONFIRM') }}
        </x-forms.buttons.action>
    </div>
</div>
