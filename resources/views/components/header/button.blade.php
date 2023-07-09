@props(['class' => '', 'icon'])
<a class="flex-shrink-0 px-5 py-4 cursor-pointer {{ $class }}" {{ $attributes }}>
    <svg class='inline my-0 fill-neutral-400 w-4 h-4 mr-0 ml-0'><use xlink:href='#{{ $icon }}' /></svg>
</a>
