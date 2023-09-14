@props(['flags', 'src'])
{{-- This is a video file: put html5 player --}}
<video width="auto" height="auto" id='image' controls
	class='absolute m-auto w-auto h-auto'
	{{ $attributes }}
	autobuffer {{ $flags->can_autoplay ? 'autoplay' : '' }}
	>
	<source src='{{ $src }}' />
	Your browser does not support the video tag.
</video>
