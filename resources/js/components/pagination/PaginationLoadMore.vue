<template>
	<div v-if="hasMore" class="flex justify-center w-full py-4">
		<Button :label="buttonLabel" :loading="loading" :disabled="loading" severity="secondary" class="rounded" @click="emit('loadMore')" />
	</div>
</template>
<script setup lang="ts">
import { computed } from "vue";
import Button from "primevue/button";

const props = defineProps<{
	loading: boolean;
	hasMore: boolean;
	remaining?: number;
	resourceType?: "photos" | "albums";
	loadingLabel?: string;
	loadMoreLabel?: string;
}>();

const emit = defineEmits<{
	loadMore: [];
}>();

const buttonLabel = computed(() => {
	if (props.loading) {
		return props.loadingLabel ?? "Loading...";
	}
	if (props.remaining !== undefined && props.remaining > 0) {
		const type = props.resourceType === "albums" ? "albums" : "photos";
		return `Load More (${props.remaining} ${type} remaining)`;
	}
	return props.loadMoreLabel ?? "Load More";
});
</script>
