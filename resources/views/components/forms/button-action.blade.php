<a id="basicModal__action"
    class="basicModal__button pt-3 pb-4 flex-shrink border-t border-t-dark-800
    cursor-default inline-block w-full font-bold text-center transition-colors select-none text-sky-500
    hover:bg-sky-500 hover:text-white rounded-br-md"
    data-tabindex="{{ Helpers::data_index() }}" {{ $attributes }}
    wire:loading.attr="disabled">{{ $slot }}
</a>
