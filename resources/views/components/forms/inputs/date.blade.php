@props(['class' => '', 'has_error' => false])
<input {{ $attributes }} class="pt-1 pb-0 px-0.5 h-7 border-b border-b-solid
placeholder:text-dark-200
hover:border-b-sky-400 focus:border-b-sky-400 border-b-neutral-800
@if($has_error) bg-red-700/10 text-red-400 @else text-white bg-transparent @endif
{{ $class }}" type="datetime-local" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}" />