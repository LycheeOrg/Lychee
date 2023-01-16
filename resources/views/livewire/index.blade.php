
<div id="lychee_application_container" class="vflex-item-stretch hflex-container" style="padding-bottom: 62px;">
	<!-- leftMenu -->
	<livewire:components.left-menu>

	@if($mode === App\Enum\Livewire\PageMode::GALLERY)
	<livewire:pages.gallery albumId="{{$this->albumId}}" photoId="{{$this->photoId}}" />
	@elseif($mode === App\Enum\Livewire\PageMode::SETTINGS)
	<livewire:pages.settings />
	@else
		DO NOTHING FOR NOW;
	@endif
	{{-- @include('includes.footer') --}}
</div>
