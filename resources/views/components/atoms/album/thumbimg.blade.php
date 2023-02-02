@props(['type' => '', 'thumb' => '', 'thumb2x' => ''])
@php
$isVideo = Str::contains($type, 'video');
@endphp
<span class="thumbimg {{-- {{ $isVideo ? "video" : ""}} --}} ">
	<img
		alt='Album thumbnail'
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
		src='{{ URL::asset('img/no_images.svg') }}'
		@if ($thumb != "")
			data-src='{{ URL::asset($thumb) }}'
		@endif
	@endif

	@if ($thumb2x != "")
		data-srcset='{{ URL::asset($thumb2x) }} 2x'
	@endif

	data-overlay='false'
	draggable='false' />
</span>
