@props(['class', 'icon'])
<a class="inline-block px-3 py-2.5 cursor-pointer {{ $class }} transition-all duration-300 ease-in h-full" {{ $attributes }}>
<x-icons.iconic icon="{{ $icon }}" fill='' class="my-0 w-4 h-4 mr-0 ml-0" />
</a>
