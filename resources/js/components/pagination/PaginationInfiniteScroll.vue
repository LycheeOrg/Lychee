<template>
	<div ref="sentinel" class="w-full h-1" />
	<div v-if="loading" class="flex justify-center w-full py-4">
		<ProgressSpinner style="width: 30px; height: 30px" strokeWidth="4" />
	</div>
</template>
<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from "vue";
import ProgressSpinner from "primevue/progressspinner";

const props = defineProps<{
	loading: boolean;
	hasMore: boolean;
	rootMargin?: string;
	threshold?: number;
}>();

const emit = defineEmits<{
	loadMore: [];
}>();

const sentinel = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

function handleIntersect(entries: IntersectionObserverEntry[]) {
	const entry = entries[0];
	if (entry.isIntersecting && props.hasMore && !props.loading) {
		emit("loadMore");
	}
}

function setupObserver() {
	if (!sentinel.value) return;

	observer = new IntersectionObserver(handleIntersect, {
		root: null,
		rootMargin: props.rootMargin ?? "200px",
		threshold: props.threshold ?? 0,
	});

	observer.observe(sentinel.value);
}

function cleanupObserver() {
	if (observer) {
		observer.disconnect();
		observer = null;
	}
}

onMounted(() => {
	setupObserver();
});

onUnmounted(() => {
	cleanupObserver();
});

watch(
	() => props.hasMore,
	(hasMore) => {
		if (!hasMore) {
			cleanupObserver();
		} else if (!observer && sentinel.value) {
			setupObserver();
		}
	},
);
</script>
