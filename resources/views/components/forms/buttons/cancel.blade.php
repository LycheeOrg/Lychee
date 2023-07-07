@props(['class' => 'w-full'])
<a class="basicModal__button pt-3 pb-4 flex-shrink-2
    cursor-pointer inline-block font-bold text-center transition-colors ease-in-out select-none
    hover:text-red-800 {{ $class }}"
    data-tabindex="{{ Helpers::data_index() }}" {{ $attributes }}>
    {{ $slot }}
</a>
