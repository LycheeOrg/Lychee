@props(['class' => '', 'has_error' => false])
<input class="w-full pt-1 pb-0 px-0.5 h-7 
border-b border-b-solid border-b-neutral-800
placeholder:text-dark-200
hover:border-b-red-700 focus:border-b-red-700
@if($has_error) bg-red-700/10 text-red-400 @else text-white bg-transparent @endif
{{ $class }}"
{{ $attributes }} type="password" />