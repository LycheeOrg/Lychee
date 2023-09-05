@props(['alt', 'src', 'srcset' => '', 'class' => 'top-7 bottom-7 left-7 right-7'])
<img id='image' alt='{{ $alt }}'
    class='absolute m-auto w-auto h-auto
max-w-[calc(100%-56px)] max-h-[calc(100%-56px)]
animate-zoomIn
{{ $class }}'
	{{ $attributes }}
	src='{{ $src }}' srcset='{{ $srcset }}' />
