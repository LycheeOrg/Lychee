@props(['class' => 'absolute w-4 h-4 top-1 left-0 ', 'disabled' => false])
<input class="appearance-none text-sky-500 border-none rounded box-shadow bg-dark-500
checked:before:content-['\✔']
before:absolute before:text-center before:text-base/4 before:w-auto before:h-auto
before:top-0 before:left-0 before:right-0 before:bottom-0
{{ $class }}" type='checkbox' {{ $attributes }}
@disabled($disabled) />
