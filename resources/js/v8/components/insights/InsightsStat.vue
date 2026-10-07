<template>
	<div class="flex flex-col gap-1 min-w-0">
		<span class="text-sm text-muted">{{ props.label }}</span>
		<span class="text-2xl font-bold text-highlighted truncate" dir="auto">{{ props.value }}</span>
		<span v-if="props.note" class="text-xs text-dimmed">{{ props.note }}</span>
	</div>
</template>
<script setup lang="ts">
const props = defineProps<{ label: string; value: string; note?: string }>();
</script>
