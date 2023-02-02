<div id="imageview" @class(
	["overlay-container",
	"fadeIn",
	"active",
	// "full" // Disabled for now
	]) style="display: block;">
@if ($photo->isVideo()) {{-- This is a video file: put html5 player --}}
<video
		width="auto"
		height="auto"
		id='image'
		controls
		class='{{ $visibleControls === true ? "" : "full" }}'
		autobuffer
		{{ $autoplay ? "autoplay" : ""}}
		data-tabindex='{{ Helpers::data_index() }}'
		><source src='{{ URL::asset($data->original->url) }}'>Your browser does not support the video tag.</video>
@elseif($photo->isRaw()) {{-- This is a raw file: put a place holder --}}
	<img
		id='image'
		alt='big'
		class='{{ $visibleControls === true ? "" : "full" }}'
		src='{{ URL::asset('img/placeholder.png') }}'
		draggable='false'
		data-tabindex='{{ Helpers::data_index() }}'
		/>
@elseif ($photo->live_photo_short_path === null) {{-- This is a normal image: medium or original --}}
	@if ($photo->size_variants->medium !== null)
		<img
			id='image'
			alt='medium'
			class='{{ $visibleControls === true ? "" : "full" }}'
			src='{{ URL::asset($photo->size_variants->medium->url) }}'
			@if ($photo->size_variants->medium2x !== null)
				srcset='{{ URL::asset($photo->size_variants->medium->url) }} {{ $photo->size_variants->medium->width }}w,
				{{ URL::asset($photo->size_variants->medium2x->url) }} {{ $photo->size_variants->medium2x->width }}w'
			@endif
			data-tabindex='{{ Helpers::data_index() }}'
			/>
	@else
		<img
			id='image'
			alt='big'
			class='{{ $visibleControls === true ? "" : "full" }}'
			src='{{ URL::asset($photo->size_variants->original->url) }}'
			draggable='false'
			data-tabindex='{{ Helpers::data_index() }}'
			/>
	@endif
@elseif ($photo->size_variants->medium !== null) {{-- This is a livephoto : medium --}}
	<div
		id='livephoto'
		data-live-photo
		data-proactively-loads-video='true'
		data-photo-src='{{ URL::asset($photo->size_variants->medium->url) }}'
		data-video-src='{{ URL::asset($photo->livePhotoUrl) }}'
		style='width: {{ $photo->size_variants->medium->width }}px; height: {{ $photo->size_variants->medium->height }}px'
		data-tabindex='{{ Helpers::data_index() }}'
		>
	</div>
@else  {{-- This is a livephoto : full --}}
	<div
		id='livephoto'
		data-live-photo
		data-proactively-loads-video='true'
		data-photo-src='{{ URL::asset($photo->size_variants->original->url) }}'
		data-video-src='{{ URL::asset($photo->livePhotoUrl) }}'
		style='width: {{ $photo->size_variants->original->width }}px; height: {{ $photo->size_variants->original->height }}px'
		data-tabindex='{{ Helpers::data_index() }}'
		>
	</div>
@endif
<livewire:components.photo-overlay :photo="$photo" />
{{-- <div class='arrow_wrapper arrow_wrapper--previous'><a id='previous'>${build.iconic("caret-left")}</a></div> --}}
{{-- <div class='arrow_wrapper arrow_wrapper--next'><a id='next'>${build.iconic("caret-right")}</a></div> --}}
</div>
