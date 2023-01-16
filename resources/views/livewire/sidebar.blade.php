<div>
@if($isOpen)
	@if($photo == null)
	<div class="sidebar active">
		<livewire:sidebar.album :album="$this->album" />
	</div>
	@else
	<div class="sidebar active">
		<livewire:sidebar.photo :album="$this->album" :photo="$this->photo" />
	</div>
	@endif
@endif
</div>