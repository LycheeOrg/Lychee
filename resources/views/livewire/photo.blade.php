<div id="imageview" class="fadeIn full" style="display: block;">
@if (Str::contains($data['type'], 'video'))
	<video
		width="auto"
		height="auto"
		id='image'
		controls
		class='{{ $visibleControls === true ? "" : "full" }}'
		autobuffer
		{{autoplay ? "autoplay" : ""}}
		data-tabindex='{{ Helpers::data_index() }}'
		><source src='{{ $data['url'] }}'>Your browser does not support the video tag.</video>
@elseif(Str::contains($data['type'], 'raw'))
	<img
		id='image'
		class='{{ $visibleControls === true ? "" : "full" }}'
		src='img/placeholder.png'
		draggable='false'
		alt='big'
		data-tabindex='{{ Helpers::data_index() }}'
		/>
@else
	@if ($data['livePhotoUrl'] === "" || $data['livePhotoUrl'] === null)
		@if ($data['medium'] !== "")
			<img
				id='image'
				class='{{ $visibleControls === true ? "" : "full" }}'
				src='{{ $data['medium'] }}'
				@if ($data['medium2x'] !== "")
					srcset='{{ $data['medium'] }} {{ intval($data['medium_dim']) }}w, {{ $data['medium2x'] }} {{ intval($data['medium2x_dim']) }}w'
				@endif
				data-tabindex='{{ Helpers::data_index() }}'
				/>
		@else
			<img
				id='image'
				class='{{ $visibleControls === true ? "" : "full" }}'
				src='{{ $data['url'] }}'
				draggable='false'
				alt='big'
				data-tabindex='{{ Helpers::data_index() }}'
				/>
		@endif
	@else
		@if ($data['medium'] !== "")
		@php
			$medium_dims = explode('x',$data['medium_dim']);
			$medium_width = $medium_dims[0];
			$medium_height = $medium_dims[1];
		@endphp
		<div
			id='livephoto'
			data-live-photo
			data-proactively-loads-video='true'
			data-photo-src='{{ $data['medium'] }}'
			data-video-src='{{ $data['livePhotoUrl'] }}'
			style='width: {{ $medium_width }}px; height: {{ $medium_height }}px'
			data-tabindex='{{ Helpers::data_index() }}'
			>
		</div>
		@else
		<div
			id='livephoto'
			data-live-photo
			data-proactively-loads-video='true'
			data-photo-src='{{ $data['url'] }}'
			data-video-src='{{ $data['livePhotoUrl'] }}'
			style='width: {{ $data['width'] }}px; height: {{ $data['height'] }}px'
			data-tabindex='{{ Helpers::data_index() }}'
			>
		</div>
		@endif
	@endif
@endif
<livewire:photo-overlay :data="$data" />
{{-- <div class='arrow_wrapper arrow_wrapper--previous'><a id='previous'>${build.iconic("caret-left")}</a></div> --}}
{{-- <div class='arrow_wrapper arrow_wrapper--next'><a id='next'>${build.iconic("caret-right")}</a></div> --}}
</div>
