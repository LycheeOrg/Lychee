@props(['icon'])
<li class="px-4">
    <a
        {{ $attributes }}
        class="p-2 py-1 cursor-pointer rounded-lg text-text-main-400 hover:text-text-main-100 whitespace-nowrap">
		<x-icons.iconic icon="{{ $icon }}" class="w-3 h-3" />
        <span class="ml-3 whitespace-nowrap text-base">
            {{ $slot }}
        </span>
    </a>
</li>
