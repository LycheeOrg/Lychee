@props(['type' => '', 'thumb' => '', 'thumb2x' => '', 'class' => ''])
@php
    $isVideo = Str::contains($type, 'video');
@endphp
<span
    class="thumbimg absolute w-full
	bg-neutral-800
	shadow-md shadow-black/25
	border-solid border border-neutral-400
	ease-out transition-transform
	{{ $class }}
	{{-- {{ $isVideo ? "video" : ""}} --}} ">
    <img alt='Album thumbnail' @class(['w-full h-full m-0 p-0 border-0', 'lazyload' => !$isVideo])
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
		@if ($thumb != '')
			data-src='{{ URL::asset($thumb) }}'
		@endif
    @endif

    @if ($thumb2x != '')
        data-srcset='{{ URL::asset($thumb2x) }} 2x'
    @endif

    data-overlay='false'
    draggable='false' />
</span>
