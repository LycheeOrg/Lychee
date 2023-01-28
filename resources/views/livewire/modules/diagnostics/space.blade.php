<div>@if(count($space) > 0)<pre>
    {{ $title }}
    {{ str_repeat('-', Str::length($title)) }}
@foreach ($space as $spaceLine)
        {{ $spaceLine }}
@endforeach
</pre>
@else<a wire:click="getSize" wire:loading.remove class="basicModal__button">{{ __('lychee.DIAGNOSTICS_GET_SIZE') }}</a><pre wire:loading>
    {{ $title }}
    {{ str_repeat('-', Str::length($title)) }}
        {{ __('lychee.LOADING') }} ...

</pre>
@endif</div>