
<div id="lychee_application_container" class="vflex-item-stretch hflex-container">
	<!-- leftMenu -->
	<livewire:components.left-menu>

	@if($page_mode === App\Enum\Livewire\PageMode::GALLERY)
	<livewire:pages.gallery albumId="{{$this->albumId}}" photoId="{{$this->photoId}}" key="gallery-{{ now() }}" />
	@elseif($page_mode === App\Enum\Livewire\PageMode::SETTINGS)
	<livewire:pages.settings />
	@else
		DO NOTHING FOR NOW;
	@endif
</div>
