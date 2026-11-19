<template>
	<UCard as="section" class="w-full" :ui="{ header: 'flex flex-wrap items-center justify-between gap-2' }">
		<template #header>
			<h2 class="text-lg font-bold text-highlighted">{{ props.title }}</h2>
			<div v-if="$slots.actions" class="flex flex-wrap items-center gap-2">
				<slot name="actions" />
			</div>
		</template>
		<slot />
	</UCard>
</template>
<script setup lang="ts">
const props = defineProps<{ title: string }>();
</script>
