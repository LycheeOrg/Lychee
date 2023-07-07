@props(['class' => ''])
<a class="basicModal__button pt-3 pb-4 flex-shrink 
    cursor-pointer inline-block w-full font-bold text-center transition-colors select-none
    text-sky-500 hover:bg-sky-500 hover:text-white {{ $class }}"
    data-tabindex="{{ Helpers::data_index() }}" {{ $attributes }}
    wire:loading.attr="disabled">{{ $slot }}
</a>
