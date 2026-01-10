<template>
	<div ref="sentinel" class="w-full h-4" />
	<div v-if="loading" class="flex justify-center w-full py-4">
		<ProgressSpinner style="width: 30px; height: 30px" strokeWidth="4" />
	</div>
</template>
<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from "vue";
import ProgressSpinner from "primevue/progressspinner";
import { useLycheeStateStore } from "@/stores/LycheeState";

const LycheeState = useLycheeStateStore();

const props = defineProps<{
	loading: boolean;
	hasMore: boolean;
	rootMargin?: string;
	threshold?: number;
	resourceType?: "photos" | "albums";
}>();

const emit = defineEmits<{
	loadMore: [];
}>();

const sentinel = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;
let isEmitting = false;
let scrollContainer: HTMLElement | null = null;

function handleIntersect(entries: IntersectionObserverEntry[]) {
	const entry = entries[0];
	console.debug(
		`[InfiniteScroll] intersect: isIntersecting=${entry.isIntersecting}, hasMore=${props.hasMore}, loading=${props.loading}, isEmitting=${isEmitting}`,
	);
	// Guard against duplicate emissions during rapid intersection callbacks
	if (entry.isIntersecting && props.hasMore && !props.loading && !isEmitting) {
		// console.log("[InfiniteScroll] => emitting loadMore");
		isEmitting = true;
		emit("loadMore");
		// Reset the flag after a brief delay to allow the loading state to propagate
		requestAnimationFrame(() => {
			isEmitting = false;
		});
	}
}

function findScrollContainer(el: HTMLElement | null): HTMLElement | null {
	while (el) {
		const style = getComputedStyle(el);
		if (style.overflowY === "auto" || style.overflowY === "scroll") {
			return el;
		}
		el = el.parentElement;
	}
	return null;
}

function getRootMargin(): string {
	if (props.rootMargin) return props.rootMargin;
	// Default to 300vh (3 full viewport heights)
	const vh = window.innerHeight;
	const coef = props.resourceType === "albums" ? LycheeState.albums_infinite_scroll_threshold : LycheeState.photos_infinite_scroll_threshold;
	return `${vh * coef}px`;
}

function setupObserver() {
	if (!sentinel.value) return;

	// Find the scroll container (e.g., #galleryView)
	scrollContainer = findScrollContainer(sentinel.value);
	const rootMargin = getRootMargin();
	console.debug("[InfiniteScroll] scroll container:", scrollContainer?.id || scrollContainer?.tagName || "viewport", "rootMargin:", rootMargin);

	observer = new IntersectionObserver(handleIntersect, {
		root: scrollContainer,
		rootMargin: rootMargin,
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

onMounted(setupObserver);

onUnmounted(cleanupObserver);

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

// When loading finishes, check if sentinel is still visible and trigger another load
watch(
	() => props.loading,
	(loading, wasLoading) => {
		console.debug(`[InfiniteScroll] loading changed: ${wasLoading} -> ${loading}, hasMore=${props.hasMore}`);
		if (wasLoading && !loading && props.hasMore && sentinel.value) {
			// Check if sentinel is still in view after loading completed
			const sentinelRect = sentinel.value.getBoundingClientRect();
			const rootMarginPx = parseInt(getRootMargin(), 10);

			let inView: boolean;
			if (scrollContainer) {
				const containerRect = scrollContainer.getBoundingClientRect();
				inView = sentinelRect.top < containerRect.bottom + rootMarginPx;
			} else {
				inView = sentinelRect.top < window.innerHeight + rootMarginPx;
			}

			console.debug(`[InfiniteScroll] post-load check: sentinel.top=${sentinelRect.top}, margin=${rootMarginPx}, inView=${inView}`);
			if (inView && !isEmitting) {
				console.debug("[InfiniteScroll] => emitting loadMore (post-load)");
				isEmitting = true;
				emit("loadMore");
				requestAnimationFrame(() => {
					isEmitting = false;
				});
			}
		}
	},
);
</script>
