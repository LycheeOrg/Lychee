<template>
	<div ref="sentinel" class="w-full h-4" />
	<div v-if="loading" class="flex justify-center w-full py-4">
		<Spinner class="text-2xl" />
	</div>
</template>
<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from "vue";
import Spinner from "@/v8/components/Spinner.vue";
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

// Sentinel element acts as an invisible trigger point for loading more content
const sentinel = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;
// Flag to prevent duplicate loadMore emissions during rapid intersection callbacks
// Without this, IntersectionObserver can fire multiple times before loading state updates
let isEmitting = false;
// Reference to the scroll container (e.g., #galleryView) for proper viewport calculation
let scrollContainer: HTMLElement | null = null;

function handleIntersect(entries: IntersectionObserverEntry[]) {
	const entry = entries[0];
	if (LycheeState.is_debug_enabled) {
		console.debug(
			`[InfiniteScroll] intersect: isIntersecting=${entry.isIntersecting}, hasMore=${props.hasMore}, loading=${props.loading}, isEmitting=${isEmitting}`,
		);
	}
	if (entry.isIntersecting && props.hasMore && !props.loading && !isEmitting) {
		isEmitting = true;
		emit("loadMore");
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
	const vh = window.innerHeight;
	const coef = props.resourceType === "albums" ? LycheeState.albums_infinite_scroll_threshold : LycheeState.photos_infinite_scroll_threshold;
	return `${vh * coef}px`;
}

function setupObserver() {
	if (!sentinel.value) return;

	scrollContainer = findScrollContainer(sentinel.value);
	const rootMargin = getRootMargin();
	if (LycheeState.is_debug_enabled) {
		console.debug("[InfiniteScroll] scroll container:", scrollContainer?.id || scrollContainer?.tagName || "viewport", "rootMargin:", rootMargin);
	}

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

watch(
	() => props.loading,
	(loading, wasLoading) => {
		if (LycheeState.is_debug_enabled) {
			console.debug(`[InfiniteScroll] loading changed: ${wasLoading} -> ${loading}, hasMore=${props.hasMore}`);
		}
		if (wasLoading && !loading && props.hasMore && sentinel.value) {
			const sentinelRect = sentinel.value.getBoundingClientRect();
			const rootMarginPx = parseInt(getRootMargin(), 10);

			let inView: boolean;
			if (scrollContainer) {
				const containerRect = scrollContainer.getBoundingClientRect();
				inView = sentinelRect.top < containerRect.bottom + rootMarginPx;
			} else {
				inView = sentinelRect.top < window.innerHeight + rootMarginPx;
			}

			if (LycheeState.is_debug_enabled) {
				console.debug(`[InfiniteScroll] post-load check: sentinel.top=${sentinelRect.top}, margin=${rootMarginPx}, inView=${inView}`);
			}
			if (inView && !isEmitting) {
				if (LycheeState.is_debug_enabled) {
					console.debug("[InfiniteScroll] => emitting loadMore (post-load)");
				}
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
