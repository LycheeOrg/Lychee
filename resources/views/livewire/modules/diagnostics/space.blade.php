<div><p class="font-mono">
    {{ $title }}
    {{ str_repeat('-', Str::length($title)) }}
@if(count($space) > 0)@foreach ($space as $spaceLine)
        {{ $spaceLine }}
@endforeach
@else
        <a wire:click="getSize" wire:loading.remove class="basicModal__button
    ml-5
    pt-3 pb-4 flex-shrink border-t border-t-dark-800 w-96
    cursor-pointer inline-block font-bold text-center transition-colors select-none text-sky-500
    hover:bg-sky-500 hover:text-white rounded-md
">{{ __('lychee.DIAGNOSTICS_GET_SIZE') }}</a><span wire:loading class="text-sky-500 font-bold">{{ __('lychee.LOADING') }} ...</span>
@endif
</p>
</div>