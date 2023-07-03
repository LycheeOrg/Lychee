
<main @class(['sm:ml-64' => $isOpen ?? false])>
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
		@case(App\Enum\Livewire\PageMode::JOBS)
		<livewire:pages.jobs />
		@break
		@case(App\Enum\Livewire\PageMode::DIAGNOSTICS)
		<livewire:pages.diagnostics />
		@break
		@case(App\Enum\Livewire\PageMode::USERS)
		<livewire:pages.users />
		@break
		@case(App\Enum\Livewire\PageMode::PROFILE)
		<livewire:pages.profile />
		@break
		@case(App\Enum\Livewire\PageMode::LANDING)
		<livewire:pages.landing />
		@break
		@default
		DO NOTHING FOR NOW;
	@endswitch
</main>
