<div>@if(count($space) > 0)<pre>
    {{ $title }}
    {{ str_repeat('-', Str::length($title)) }}
@foreach ($space as $spaceLine)
        {{ $spaceLine }}
@endforeach
</pre>
@else<a wire:click="getSize" wire:loading.remove class="basicModal__button
    ml-5
    pt-3 pb-4 flex-shrink border-t border-t-dark-800 w-96
    cursor-pointer inline-block font-bold text-center transition-colors select-none text-sky-500
    hover:bg-sky-500 hover:text-white rounded-md
">{{ __('lychee.DIAGNOSTICS_GET_SIZE') }}</a><pre wire:loading>
    {{ $title }}
    {{ str_repeat('-', Str::length($title)) }}
        {{ __('lychee.LOADING') }} ...

</pre>
@endif</div>