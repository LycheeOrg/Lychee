<div>
@if($isOpen)
<div class="basicModalContainer basicModalContainer--fadeIn" data-closable="true">
	<div class="basicModal basicModal--fadeIn " role="dialog">
		@livewire($type, compact('params'))
	</div>
</div>
@endif
</div>
