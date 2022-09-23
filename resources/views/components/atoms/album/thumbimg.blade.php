@props(['type' => '', 'thumb' => '', 'thumb2x' => ''])
@php
$isVideo = Str::contains($type, 'video');
@endphp
<span class="thumbimg
	{{-- {{ $isVideo ? "video" : ""}} --}}
	">
	<img
	@if(!$isVideo) class='lazyload' @endif

	@if ($thumb == 'uploads/thumb/')
		@if ($isVideo)
			src='{{ URL::asset('img/play-icon.png') }}'
		@else
			src='{{ URL::asset('img/placeholder.png') }}'

			@if (Str::contains($this->type, 'raw'))
				data-src='{{ URL::asset('img/no_images.svg') }}'
			@else
			@endif
		@endif

	@else
		src='{{ URL::asset('img/placeholder.png') }}'
		data-src='{{ URL::asset($thumb) }}'
	@endif


	@if ($thumb2x != "")
		data-srcset='{{ URL::asset($thumb2x) }} 2x'
	@endif

	alt='Photo thumbnail'
	data-overlay='false'
	draggable='false'>
</span>
