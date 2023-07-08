<div>
@if($isOpen)
<div class="basicModalContainer transition-opacity ease-in bg-black/70 z-50 fixed flex items-center justify-center w-full h-full top-0 left-0 box-border opacity-100" data-closable="true">
	<div class="basicModal transition-opacity ease-in opacity-100 bg-gradient-to-b from-neutral-600 to-neutral-700 relative w-[500px] text-sm rounded-md text-neutral-400 " role="dialog" x-on:click.away="$wire.closeModal()">
		@livewire($type, compact('params'))
		@if($close_text !== '')
		<div class="basicModal__buttons">
			<x-forms.buttons.cancel wire:click="closeModal" class="border-t border-t-black/20 w-full hover:bg-white/[.02]">{{ Lang::get($close_text) }}</x-forms.buttons.cancel>
		</div>
		@endif
	</div>
</div>
@endif
</div>
