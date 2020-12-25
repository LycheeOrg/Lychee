<div class="content contentZoomIn">
	@if($info['albums']->count() > 0)
		<div class='divider'><h1>{{ Lang::get('ALBUMS') }}</h1></div>
		@foreach ($info['albums'] as $data)
			@include('livewire.parts.album')
		@endforeach
	@endif
	@php
	@endphp
	@if(count($photos) > 0)
	<div class='divider'><h1>{{ Lang::get('PHOTOS') }}</h1></div>
	@foreach ($photos as $data)
		@include('livewire.parts.photo')
	@endforeach
	@endif
</div>