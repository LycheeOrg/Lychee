<template>
	<div v-if="hasMore" class="flex justify-center w-full py-4">
		<Button :label="buttonLabel" :loading="loading" :disabled="loading" severity="secondary" class="rounded" @click="emit('loadMore')" />
	</div>
</template>
<script setup lang="ts">
import { computed } from "vue";
import { sprintf } from "sprintf-js";
import Button from "primevue/button";
import { trans } from "laravel-vue-i18n";

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
		return props.loadingLabel ?? trans("gallery.pagination.loading");
	}
	if (props.remaining !== undefined && props.remaining > 0) {
		const key = props.resourceType === "albums" ? "gallery.pagination.load_more_albums" : "gallery.pagination.load_more_photos";
		return sprintf(trans(key), props.remaining);
	}
	return props.loadMoreLabel ?? trans("gallery.pagination.load_more");
});
</script>
