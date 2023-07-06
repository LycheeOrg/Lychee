@props(['action', 'class' => '', 'icon'])
<a class="flex-shrink-0 pt-4 pr-3 pb-4 pl-5 cursor-pointer {{ $class }}" wire:click="{{ $action }}">
    <svg class='inline my-0 fill-neutral-400 w-4 h-4 mr-0 ml-0'><use xlink:href='#{{ $icon }}' /></svg>
</a>
