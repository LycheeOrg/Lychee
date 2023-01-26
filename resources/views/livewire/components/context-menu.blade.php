<div class="basicContextContainer" @if(!$isOpen)style="display:none"@endif>
	@if($isOpen)
		@livewire('context-menus.'. $type, ['params' => $params])
	@endif
</div>