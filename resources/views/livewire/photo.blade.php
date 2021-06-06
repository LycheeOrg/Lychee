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
		><source src='{{ URL::asset($data['url']) }}'>Your browser does not support the video tag.</video>
@elseif(Str::contains($data['type'], 'raw'))
	<img
		id='image'
		class='{{ $visibleControls === true ? "" : "full" }}'
		src='{{ URL::asset('img/placeholder.png') }}'
		draggable='false'
		alt='big'
		data-tabindex='{{ Helpers::data_index() }}'
		/>
@else
	@if ($data['livePhotoUrl'] === "" || $data['livePhotoUrl'] === null)
		@if ($data['sizeVariants']['medium'] !== null)
			<img
				id='image'
				class='{{ $visibleControls === true ? "" : "full" }}'
				src='{{ URL::asset($data['sizeVariants']['medium']['url']) }}'
				@if ($data['sizeVariants']['medium2x'] !== null)
					srcset='{{ URL::asset($data['sizeVariants']['medium']['url']) }} {{ $data['sizeVariants']['medium']['width'] }}w,
					{{ URL::asset($data['sizeVariants']['medium2x']['url']) }} {{ $data['sizeVariants']['medium2x']['width'] }}w'
				@endif
				data-tabindex='{{ Helpers::data_index() }}'
				/>
		@else
			<img
				id='image'
				class='{{ $visibleControls === true ? "" : "full" }}'
				src='{{ URL::asset($data['url']) }}'
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
			data-photo-src='{{ URL::asset($data['sizeVariants']['medium']['url']) }}'
			data-video-src='{{ URL::asset($data['livePhotoUrl']) }}'
			style='width: {{ $medium_width }}px; height: {{ $medium_height }}px'
			data-tabindex='{{ Helpers::data_index() }}'
			>
		</div>
		@else
		<div
			id='livephoto'
			data-live-photo
			data-proactively-loads-video='true'
			data-photo-src='{{ URL::asset($data['url']) }}'
			data-video-src='{{ URL::asset($data['livePhotoUrl']) }}'
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
