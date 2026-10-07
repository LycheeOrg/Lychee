<template>
	<div class="flex flex-wrap gap-1" role="radiogroup">
		<UButton
			v-for="option in props.options"
			:key="option.value"
			size="xs"
			color="neutral"
			role="radio"
			:aria-checked="option.value === model"
			:variant="option.value === model ? 'soft' : 'ghost'"
			:label="option.label"
			@click="model = option.value"
		/>
	</div>
</template>
<script setup lang="ts" generic="T extends string">
const model = defineModel<T>({ required: true });
const props = defineProps<{ options: { value: T; label: string }[] }>();
</script>
