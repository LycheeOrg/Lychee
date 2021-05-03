<div id="container" style="padding-bottom: 62px;">
	<!-- Loading -->
	<div id="loading"></div>
	
		<livewire:header :mode="$mode" :album="$album" />

		@if($mode == 'albums')
		<livewire:albums />

		@elseif($mode == 'album')
		<livewire:album :album="$album" />

		@elseif($mode == 'photo')
		<livewire:album :album="$album" />
		<livewire:photo :album="$album" :photo="$photo" />

		@elseif($mode == 'map')
		Later...

		@endif
	{{-- @livewire('left-menu') --}}
	{{-- @livewire('albums') --}}
	
	{{-- @include('includes.footer') --}}
</div>
	