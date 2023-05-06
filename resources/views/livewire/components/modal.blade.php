<div>
@if($isOpen)
<div class="basicModalContainer basicModalContainer--fadeIn" data-closable="true">
	<div class="basicModal basicModal--fadeIn " role="dialog" x-on:click.away="$wire.closeModal()">
		@livewire($type, compact('params'))
		@if($close_text !== '')
		<div class="basicModal__buttons">
			<a id="basicModal__cancel" class="basicModal__button"  wire:click="closeModal">{{ Lang::get($close_text) }}</a>
		</div>
		@endif
	</div>
</div>
@endif
</div>
