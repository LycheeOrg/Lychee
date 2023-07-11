@props(['class' => 'w-full', 'mode', 'current'])
<li @class([
    "my-2 px-2 pt-3 pb-4 cursor-pointer font-bold text-center transition-colors ease-in-out select-none
    xl:border-l-2 max-xl:border-b-2 border-solid border-sky-500
    hover:border-sky-200 hover:text-sky-200 {{ $class }}",
    'text-sky-500 ' => $mode !== $current,
    'text-sky-500 bg-sky-200/10' => $mode === $current,
]) {{ $attributes }} wire:click="setMode('{{ $mode }}')">
    {{ $slot }}
</li>
