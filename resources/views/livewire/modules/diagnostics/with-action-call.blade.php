<div class="select-none">
@if($can)
<pre class="font-mono">
    {{ $this->title }}
    {{ str_repeat('-', Str::length($this->title)) }}
@if(count($result) > 0)@foreach ($result as $resultLine)
        {{ $resultLine }}
@endforeach
@else
        <a wire:click="do" wire:loading.remove class="basicModal__button
    ml-5
    pt-3 pb-4 flex-shrink border-t border-t-bg-800 w-96
    cursor-pointer inline-block font-bold text-center transition-colors select-none text-primary-500
    hover:bg-primary-500 hover:text-text-main-0 rounded-md
">{{ $action }}</a><span wire:loading class="text-primary-500 font-bold">{{ __('lychee.LOADING') }} ...</span>
@endif
</p>
@endif
</div>