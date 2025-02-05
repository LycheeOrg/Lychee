<template>
	<div class="text-muted-color-emphasis">
		{{ $t("statistics.punch_card.less") }}
		<span v-tooltip="'= 0'" class="mx-0.5 inline-block h-3 w-3 bg-transparent border border-surface-400 dark:border-surface-700"></span>
		<span
			v-tooltip="`0 < ${props.low}`"
			class="mx-0.5 inline-block h-3 w-3 bg-sky-100 dark:bg-sky-100/60 border border-surface-400 dark:border-surface-700"
		></span>
		<span
			v-tooltip="`${props.low} < ${props.medium}`"
			class="mx-0.5 inline-block h-3 w-3 bg-sky-300 dark:bg-sky-300/80 border border-surface-400 dark:border-surface-700"
		></span>
		<span
			v-tooltip="`${props.medium} < ${props.high}`"
			class="mx-0.5 inline-block h-3 w-3 bg-sky-500 dark:bg-sky-500/80 border border-surface-400 dark:border-surface-700"
		></span>
		<span
			v-tooltip="`≥ ${props.high}`"
			class="mx-0.5 inline-block h-3 w-3 bg-sky-700 dark:bg-sky-700 border border-surface-400 dark:border-surface-700"
		></span>
		{{ $t("statistics.punch_card.more") }}
	</div>
</template>
<script setup lang="ts">
const props = defineProps<{
	low: number;
	medium: number;
	high: number;
}>();
</script>
