<template>
	<div class="flex gap-16 justify-between w-full pl-8.5 items-center">
		<div class="text-muted text-sm">
			{{ $t("statistics.punch_card.caption") }}
		</div>

		<div class="text-highlighted">
			{{ $t("statistics.punch_card.less") }}
			<UTooltip text="= 0"><span class="mx-0.5 inline-block h-3 w-3 bg-transparent border border-default"></span></UTooltip>
			<UTooltip :text="`0 < ${props.low}`"
				><span class="mx-0.5 inline-block h-3 w-3 bg-primary-100 dark:bg-primary-100/60 border border-default"></span
			></UTooltip>
			<UTooltip :text="`${props.low} < ${props.medium}`"
				><span class="mx-0.5 inline-block h-3 w-3 bg-primary-300 dark:bg-primary-300/80 border border-default"></span
			></UTooltip>
			<UTooltip :text="`${props.medium} < ${props.high}`"
				><span class="mx-0.5 inline-block h-3 w-3 bg-primary-500 dark:bg-primary-500/80 border border-default"></span
			></UTooltip>
			<UTooltip :text="`≥ ${props.high}`"
				><span class="mx-0.5 inline-block h-3 w-3 bg-primary-700 dark:bg-primary-700 border border-default"></span
			></UTooltip>
			{{ $t("statistics.punch_card.more") }}
		</div>
	</div>
</template>
<script setup lang="ts">
const props = defineProps<{
	low: number;
	medium: number;
	high: number;
}>();
</script>
