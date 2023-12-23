@props(['class' => ''])
<textarea {{ $attributes }} class="p-2 h-28 bg-transparent text-text-main-0 border border-solid border-bg-400 resize-y w-full
hover:border-primary-400 focus:border-primary-400 focus-visible:outline-none {{ $class }}">{{ $slot }}</textarea>
