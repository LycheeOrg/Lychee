<template>
	<div class="bg-elevated rounded p-4 text-center" :class="{ 'col-span-2': wide }">
		<div :class="small ? 'text-lg font-bold' : 'text-2xl font-bold'" :dir="ltr ? 'ltr' : undefined">{{ value }}</div>
		<div class="text-muted text-sm">{{ $t(label) }}</div>
	</div>
</template>

<script lang="ts" setup>
defineProps<{
	label: string;
	value: string | number;
	ltr?: boolean;
	small?: boolean;
	wide?: boolean;
}>();
</script>
