<div wire:init="loadErrors">
<p class="font-mono">
    {{ $title }}
    {{ str_repeat('-', Str::length($title)) }}
@if($ready_to_load)@forelse($this->data as $line)
        {{ $line }}
@empty{{ $error_msg }}@endforelse
@else
    <span class="text-sky-500 font-bold">    {{ __('lychee.LOADING') }} ...</span>
@endif
</p>
</div>