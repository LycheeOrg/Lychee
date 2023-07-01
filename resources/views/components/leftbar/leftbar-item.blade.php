@props(['action' => '', 'href' => '', 'click' => ''])
<li>
    <a @if ($action !== '') wire:click="$emitTo('index', 'openPage', '{{ $action }}')" @endif
        @if ($click !== '') wire:click="{{ $click }}" @endif
        @if ($href !== '') href="{{ $href }}" @endif
        class="flex items-center p-2 light:text-gray-900 rounded-lg text-gray-400 light:bg-gray-100 hover:bg-gray-700">
        {{ $slot }}
    </a>
</li>
