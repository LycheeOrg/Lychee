<a id="basicModal__action"
    class="basicModal__button pt-3 pb-4 flex-shrink border-t border-t-dark-800
    cursor-default inline-block w-full font-bold text-center transition-colors select-none text-blue-500"
    data-tabindex="{{ Helpers::data_index() }}" {{ $attributes }}
    wire:loading.attr="disabled">{{ $slot }}
</a>
