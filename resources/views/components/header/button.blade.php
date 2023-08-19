@props(['class' => '', 'fill' => 'fill-neutral-400', 'icon'])
<a class="flex-shrink-0 px-5 py-4 cursor-pointer {{ $class }}" {{ $attributes }}>
    <x-icons.iconic icon="{{ $icon }}" fill='{{ $fill }}' class="my-0 w-4 h-4 mr-0 ml-0" />
</a>
