
<div id="container" style="padding-bottom: 62px;">
	<!-- Loading -->
	<div id="loading"></div>
	<livewire:components.header :mode="$mode" :title="$this->title" />
	@if($mode === App\Http\Livewire\Pages\Fullpage::ALBUMS)
		<livewire:modules.albums/>
	@elseif($mode === App\Http\Livewire\Pages\Fullpage::ALBUM)
		<livewire:modules.album :album="$this->album" />
	@elseif($mode === App\Http\Livewire\Pages\Fullpage::PHOTO)
		<livewire:modules.photo :album="$this->album" :photo="$this->photo" />
	@elseif($mode === 'map')
		Later...
	@endif
	{{-- @livewire('left-menu') --}}
	{{-- @livewire('albums') --}}

	{{-- @include('includes.footer') --}}
	<livewire:components.modal>
</div>
