<div>
    @if ($isOpen)
        <div class="basicModalContainer transition-opacity duration-1000 ease-in animate-fadeIn
					bg-black/80 z-30 fixed flex items-center justify-center w-full
					h-full top-0 left-0 box-border opacity-100 menu-shadow"
            data-closable="true">
            <div class="basicModal transition-opacity ease-in duration-1000
						opacity-100 bg-gradient-to-b from-bg-300 to-bg-400
						relative w-[500px] text-sm rounded-md text-text-main-400 animate-moveUp
						"
                role="dialog" x-on:click.away="$wire.closeModal()">
                @livewire($type, compact('params'))
                @if ($close_text !== '')
                    <div class="basicModal__buttons">
                        <x-forms.buttons.cancel wire:click="closeModal"
                            class="border-t border-t-black/20 w-full hover:bg-white/[.02]">{{ Lang::get($close_text) }}</x-forms.buttons.cancel>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
