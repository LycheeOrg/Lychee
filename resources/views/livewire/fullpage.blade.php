<div id="container" style="padding-bottom: 62px;">
	<!-- Loading -->
	<div id="loading"></div>
	<livewire:header :mode="$mode" :album="$this->album" />
	@if($mode === App\Http\Livewire\Fullpage::ALBUMS)
		<livewire:albums />
	@elseif($mode === App\Http\Livewire\Fullpage::ALBUM)
		<livewire:album :album="$this->album" />
	@elseif($mode === App\Http\Livewire\Fullpage::PHOTO)
		<livewire:photo :album="$this->album" :photo="$photo" />
	@elseif($mode === 'map')
		Later...
	@endif
	{{-- @livewire('left-menu') --}}
	{{-- @livewire('albums') --}}

	{{-- @include('includes.footer') --}}
</div>
