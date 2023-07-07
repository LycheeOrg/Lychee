@props(['class' => ''])
<a class="basicModal__button pt-3 pb-4 flex-shrink
    cursor-pointer inline-block w-full font-bold text-center transition-colors select-none
    text-red-800 hover:text-white hover:bg-red-800 {{ $class }}"
    data-tabindex="{{ Helpers::data_index() }}" {{ $attributes }}>
    {{ $slot }}
</a>
