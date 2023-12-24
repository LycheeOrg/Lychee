@props(['if', 'locale', 'value'])
<template x-if="{{ $if }}">
	<span class="py-0.5 pl-3 text-sm">{{ $locale }}</span>
</template>
<template x-if="{{ $if }}">
	<span class="py-0.5 pl-0 text-sm" x-text="{{ $value }}"></span>
</template>
