<div id="container" style="padding-bottom: 62px;">
	<!-- Loading -->
	<div id="loading"></div>
	
		<livewire:header :mode="$mode" />

		@if($mode == 'albums')
		<livewire:albums />

		@elseif($mode == 'album')
		<livewire:album :albumId="$albumId" />

		@elseif($mode == 'photo')
		<livewire:photo :albumId="$albumId" :photoId="$photoId" />

		@elseif($mode == 'map')
		Later...

		@endif
	{{-- @livewire('left-menu') --}}
	{{-- @livewire('albums') --}}
	
	{{-- @include('includes.footer') --}}
</div>
	