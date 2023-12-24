@props(['class' => 'w-full'])
<a class="basicModal__button pt-3 pb-4 flex-shrink
    cursor-pointer inline-block font-bold text-center transition-colors ease-in-out select-none
    text-danger-800 hover:text-text-hover hover:bg-danger-800 {{ $class }}"
    {{ $attributes }}>
    {{ $slot }}
</a>
