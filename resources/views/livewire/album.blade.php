<div class="content contentZoomIn">
	@php
		Helpers::data_index_set(100);
	@endphp
	@if($info['albums']->count() > 0)
		@if(count($photos) > 0)
		<div class='divider'><h1>{{ Lang::get('ALBUMS') }}</h1></div>
		@endif
		@foreach ($info['albums'] as $data)
			@include('livewire.parts.album')
		@endforeach

		@if(count($photos) > 0)
		<div class='divider'><h1>{{ Lang::get('PHOTOS') }}</h1></div>
		@endif
	@endif
	<div class="{{ $layout }}">
	@foreach ($photos as $data)
		<x-photo :data="$data" />
	@endforeach
	</div>
</div>