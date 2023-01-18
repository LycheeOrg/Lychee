
<div id="lychee_application_container" class="vflex-item-stretch hflex-container">
	<!-- leftMenu -->
	<livewire:components.left-menu>
	@switch($page_mode)
		@case(App\Enum\Livewire\PageMode::GALLERY)
		<livewire:pages.gallery albumId="{{$this->albumId}}" photoId="{{$this->photoId}}" />
		@break
		@case(App\Enum\Livewire\PageMode::SETTINGS)
		<livewire:pages.settings />
		@break
		@case(App\Enum\Livewire\PageMode::ALL_SETTINGS)
		<livewire:pages.all-settings />
		@break
		@case(App\Enum\Livewire\PageMode::LOGS)
		<livewire:pages.logs />
		@break
		@case(App\Enum\Livewire\PageMode::DIAGNOSTICS)
		<livewire:pages.diagnostics />
		@break
		@default
		DO NOTHING FOR NOW;
	@endswitch
</div>
