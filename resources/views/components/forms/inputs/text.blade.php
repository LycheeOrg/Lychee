@props(['class' => 'w-full', 'has_error' => false])
<input class="pt-1 pb-0 px-0.5 h-7 border-b border-b-solid border-b-bg-500
placeholder:text-text-main-200
hover:border-b-primary-400 focus:border-b-primary-400
@if($has_error) bg-danger-700/10 text-danger-600 @else text-text-main-0 bg-transparent @endif
{{ $class }}"
 {{ $attributes }} type="text" />