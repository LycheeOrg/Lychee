@props(['icon'])
<li class="px-4">
    <a
        class="p-2 py-1 cursor-not-allowed rounded-lg text-text-main-400 whitespace-nowrap">
		<x-icons.iconic icon="{{ $icon }}" class="w-3 h-3" />
        <span class="ml-3 whitespace-nowrap text-base italic line-through">
            {{ $slot }}
        </span>
    </a>
</li>
