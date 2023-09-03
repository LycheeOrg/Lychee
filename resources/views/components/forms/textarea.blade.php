@props(['class' => ''])
<textarea {{ $attributes }} class="p-2 h-28 bg-transparent text-white border border-solid border-neutral-400 resize-y w-full
hover:border-sky-400 focus:border-sky-400 focus-visible:outline-none {{ $class }}">{{ $slot }}</textarea>
