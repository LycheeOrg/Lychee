@props(['class' => '', 'has_error' => false])
<input class="py-1 px-0.5 h-7 border-b border-b-solid
placeholder:text-dark-200
hover:border-b-sky-400 focus:border-b-sky-400 border-b-neutral-800
@if($has_error) bg-red-700/10 text-red-400 @else text-white bg-transparent @endif
{{ $class }}"
 {{ $attributes }} type="text" />