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

// Sentinel element acts as an invisible trigger point for loading more content
const sentinel = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;
// Flag to prevent duplicate loadMore emissions during rapid intersection callbacks
// Without this, IntersectionObserver can fire multiple times before loading state updates
let isEmitting = false;
// Reference to the scroll container (e.g., #galleryView) for proper viewport calculation
let scrollContainer: HTMLElement | null = null;

/**
 * Handle intersection observer callback when sentinel enters/exits viewport.
 * Critical edge case: IntersectionObserver can fire multiple callbacks rapidly,
 * especially when items are small or load very quickly. The isEmitting flag
 * prevents duplicate emissions until the loading state has time to propagate.
 */
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
		// requestAnimationFrame ensures this happens after the next paint
		requestAnimationFrame(() => {
			isEmitting = false;
		});
	}
}

/**
 * Walk up the DOM tree to find the first scrollable ancestor.
 * This is important because IntersectionObserver needs the correct root element
 * to calculate visibility properly. Without finding the scroll container,
 * it would use the document viewport, which breaks for nested scroll areas.
 */
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

/**
 * Calculate the rootMargin for IntersectionObserver based on viewport height and threshold.
 * The rootMargin expands the intersection area, causing the observer to trigger
 * BEFORE the sentinel actually enters the viewport. This creates a buffer zone
 * so content loads before the user scrolls all the way to the bottom.
 *
 * Example: With 3.0 threshold on 1000px viewport = 3000px rootMargin
 * -> Sentinel triggers when it's 3000px below the bottom of the viewport
 * -> This gives enough time to load content before user reaches the end
 */
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

/**
 * Critical edge case handler: When loading finishes, check if sentinel is still visible.
 *
 * Scenario: If the loaded content is small (few items) or renders quickly,
 * the sentinel might still be in the viewport after the new items are added.
 * Without this check, scrolling would stop and require manual scrolling to trigger more loading.
 *
 * Example:
 * 1. User scrolls, sentinel enters viewport with 3000px rootMargin
 * 2. Content loads but only adds 500px of height
 * 3. Sentinel is STILL within the 3000px trigger zone
 * 4. This watcher detects that and immediately triggers another load
 * 5. Process repeats until enough content fills the viewport + margin
 */
watch(
	() => props.loading,
	(loading, wasLoading) => {
		if (LycheeState.is_debug_enabled) {
			console.debug(`[InfiniteScroll] loading changed: ${wasLoading} -> ${loading}, hasMore=${props.hasMore}`);
		}
		if (wasLoading && !loading && props.hasMore && sentinel.value) {
			// Check if sentinel is still in view after loading completed
			const sentinelRect = sentinel.value.getBoundingClientRect();
			const rootMarginPx = parseInt(getRootMargin(), 10);

			// Calculate visibility relative to scroll container or viewport
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
