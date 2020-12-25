<div id="container" style="padding-bottom: 62px;">
	<!-- Loading -->
	<div id="loading"></div>
	
		<livewire:header :mode="$mode" />

		@if($mode == 'albums')
		<livewire:albums />
		@elseif($mode == 'album')
		@elseif($mode == 'photo')
		@elseif($mode == 'map')
		@endif
	{{-- @livewire('left-menu') --}}
	{{-- @livewire('albums') --}}
	
	{{-- @include('includes.footer') --}}
</div>
	