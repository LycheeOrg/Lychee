<div class="content contentZoomIn">
	@php
		Helpers::data_index_set(100);
	@endphp
	<div class="{{ $layout }}">
		@foreach ($album->photos as $data)
			<x-photo :data="$data" />
		@endforeach
	</div>
</div>