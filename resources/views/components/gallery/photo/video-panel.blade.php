@props(['flags', 'src', 'class' => 'top-7 bottom-7 left-7 right-7'])
{{-- This is a video file: put html5 player --}}
<video width="auto" height="auto" id='image' controls
	class='absolute m-auto w-auto h-auto
	max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]
	{{ $class }}
	'
	{{ $attributes }}
	autobuffer {{ $flags->can_autoplay ? 'autoplay' : '' }}
	>
	<source src='{{ $src }}' />
	Your browser does not support the video tag.
</video>
