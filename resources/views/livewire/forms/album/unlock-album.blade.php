<div class="basicModalContainer transition-opacity duration-1000 ease-in animate-fadeIn
    bg-black/80 fixed flex items-center justify-center w-full h-full top-0 left-0 box-border opacity-100"
    data-closable="true" >
    <div class="basicModal transition-opacity ease-in duration-1000
        opacity-100 bg-gradient-to-b from-bg-300 to-bg-400
        relative w-[500px] text-sm rounded-md text-text-main-400 animate-moveUp"
        role="dialog"
        >
        <div class="flex flex-wrap p-9 gap-5 justify-center align-top text-text-main-0/80">
            <x-forms.error-message field='password' />
            <form class="" wire:submit="submit">
                <div class="my-4 mx-0">
                    <p class="text-center">
                        {{ __('lychee.ALBUM_PASSWORD_REQUIRED') }}
                    </p>
                    <x-forms.inputs.password class="w-full" autocomplete="album-password"
                        x-intersect="$el.focus()" 
                        placeholder="{{ __('lychee.PASSWORD') }}"
                        wire:model="password"
                        wire:keydown.enter="submit"
                         />
                </div>
            </form>
        </div>
        <div class="flex w-full box-border">
            <x-forms.buttons.cancel class="border-t border-t-bg-800 rounded-bl-md w-full"
                @keydown.escape.window="Alpine.navigate($el.href)" wire:navigate href="{{ $back }}">
                {{ __('lychee.CANCEL') }}
            </x-forms.buttons.cancel>
            <x-forms.buttons.action class="border-t border-t-bg-800 rounded-br-md w-full"
                @keydown.enter.window="$wire.submit()" wire:click="submit">
                {{ __('lychee.ENTER') }}
            </x-forms.buttons.action>
        </div>
    </div>
</div>
