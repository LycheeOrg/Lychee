<pre wire:init="loadErrors">
    {{ $title }}
    {{ str_repeat('-', Str::length($title)) }}
    @if($ready_to_load)@forelse($this->data as $line)
    {{ $line }}
    @empty{{ $error_msg }}@endforelse
    @else
    {{ __('lychee.LOADING') }} ...
    @endif
</pre>