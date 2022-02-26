<div>
@if($isOpen)
	<div class="sidebar active">
		<livewire:sidebar.album :album="$this->album" />
	</div>
@endif
</div>