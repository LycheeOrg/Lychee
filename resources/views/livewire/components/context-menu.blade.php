<div>
@if($isOpen)
<div class="z-10 fixed flex items-center justify-center w-full h-full top-0 left-0 box-border" data-closable="true">
	<div class="py-1 box-shadow rounded-md
		border-solid border-black/80 border
		max-w-xs absolute
		bg-gradient-to-b from-dark-400 to-dark-500
		animate-scaleIn"
		role="dialog"
		x-on:click.away="$wire.closeContextMenu()"
		style="{{ $style }}">
		@livewire($type, ['params' => $params])
	</div>
</div>
@endif
</div>