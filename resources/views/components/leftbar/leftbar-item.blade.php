@props(['action' => '', 'href' => '', 'click' => '', 'icon'])
<li class="px-4">
    <a
        @if ($action !== '') wire:click="$emitTo('index', 'openPage', '{{ $action }}')" @endif
        @if ($click !== '') wire:click="{{ $click }}" @endif
        @if ($href !== '') href="{{ $href }}" @endif
        class="p-2 py-1 cursor-pointer rounded-lg text-neutral-400 hover:text-neutral-200 light:text-dark-850 light:bg-neutral-100 whitespace-nowrap ">
		<x-icons.iconic icon="{{ $icon }}" class="w-3 h-3" />
        <span class="ml-3 whitespace-nowrap text-base">
            {{ $slot }}
        </span>
    </a>
</li>
