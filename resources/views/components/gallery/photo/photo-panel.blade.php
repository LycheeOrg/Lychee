@props(['alt', 'src', 'srcset' => ''])
<img id='image' alt='{{ $alt }}'
    class='absolute m-auto w-auto h-auto animate-zoomIn bg-contain bg-center bg-no-repeat'
	{{ $attributes }}
	src='{{ $src }}' srcset='{{ $srcset }}' />
