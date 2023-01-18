<div>@if(count($space) > 0)<pre>
    {{ $title }}
    {{ str_repeat('-', Str::length($title)) }}
@foreach ($space as $spaceLine)
        {{ $spaceLine }}
@endforeach
</pre>
@else<a wire:click="getSize" class="basicModal__button">{{ Lang::get('DIAGNOSTICS_GET_SIZE') }}</a>@endif</div>