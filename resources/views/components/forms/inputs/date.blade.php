@props(['class' => '', 'has_error' => false])
<input {{ $attributes }} class="pt-1 pb-0 px-0.5 h-7 border-b border-b-solid border-b-bg-600
placeholder:text-text-main-400
hover:border-b-primary-400 focus:border-b-primary-400
@if($has_error) bg-danger-700/10 text-danger-400 @else text-text-main-0 bg-transparent @endif
{{ $class }}" type="datetime-local" pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}" />