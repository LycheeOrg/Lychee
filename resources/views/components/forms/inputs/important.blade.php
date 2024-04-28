@props(['class' => '', 'has_error' => false])
<input class="w-full pt-1 pb-0 px-0.5 h-7 border-b border-b-solid border-b-bg-800
placeholder:text-text-main-400
hover:border-b-danger-700 focus:border-b-danger-700
@if($has_error) bg-danger-700/10 text-danger-600 @else text-text-main-0 bg-transparent @endif
{{ $class }}"
{{ $attributes }} type="text" />