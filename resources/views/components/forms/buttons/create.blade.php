@props(['class' => 'w-full'])
<a class="basicModal__button pt-3 pb-4 flex-shrink 
    cursor-pointer inline-block font-bold text-center transition-colors ease-in-out select-none
    text-create-600 hover:text-text-hover hover:bg-create-700 {{ $class }}"
    {{ $attributes }}
    wire:loading.attr="disabled">{{ $slot }}
</a>
