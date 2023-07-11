@props(['class' => 'w-full', 'mode', 'current'])
<li @class([
    "my-2 px-2 pt-3 pb-4 cursor-pointer font-bold text-center transition-colors ease-in-out select-none
    xl:border-l-2 max-xl:border-b-2 border-solid border-red-800
    hover:bg-red-800 hover:text-white {{ $class }}",
    'text-red-800' => $mode !== $current,
    'text-white bg-red-800' => $mode === $current,
]) {{ $attributes }} wire:click="setMode('{{ $mode }}')">
    {{ $slot }}
</li>
