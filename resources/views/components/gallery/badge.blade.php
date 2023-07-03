@props(['class', 'icon'])
<a
    class='badge inline-block ml-1 px-2 pt-2 pb-1 border-solid border-white border border-t-0 rounded-md rounded-t-none text-white text-center {{ $class }}'>
    <svg class='iconic inline w-4 h-4 fill-white'>
        <use xlink:href='#{{ $icon }}' />
    </svg>
</a>
