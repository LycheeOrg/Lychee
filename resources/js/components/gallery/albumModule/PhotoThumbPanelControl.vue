<template>
	<!-- Star Rating Filter (visible only when rated photos exist) -->
	<div
		v-if="photosStore.hasRatedPhotos"
		role="group"
		:aria-label="$t('gallery.filter.by_rating')"
		class="inline-flex items-center gap-0.5 pr-2 mr-2 border-r border-neutral-300 dark:border-neutral-600 h-8"
	>
		<button
			v-for="star in 5"
			:key="star"
			ref="starButtons"
			type="button"
			:aria-label="$t('gallery.filter.n_stars_or_higher', { n: star.toString() })"
			:aria-pressed="photosStore.photoRatingFilter === star"
			class="p-1 cursor-pointer transition-transform duration-150 hover:scale-110 focus:outline-none rounded"
			@click="handleStarClick(star)"
			@keydown="handleKeyDown($event, star)"
		>
			<i :class="starIconClass(star)" />
		</button>
	</div>
	<!-- Layout buttons -->
	<a class="px-1 cursor-pointer group hidden sm:inline-block h-8" :title="$t('gallery.layout.squares')" @click="layout = 'square'">
		<MiniIcon icon="squares" fill="fill-transparent" :class="layoutStore.squareClass" />
	</a>
	<a class="px-1 cursor-pointer group hidden sm:inline-block h-8" :title="$t('gallery.layout.justified')" @click="layout = 'justified'">
		<MiniIcon icon="justified" fill="" :class="layoutStore.justifiedClass" />
	</a>
	<a class="px-1 cursor-pointer group hidden sm:inline-block h-8" :title="$t('gallery.layout.masonry')" @click="layout = 'masonry'">
		<MiniIcon icon="masonry" fill="fill-transparent" :class="layoutStore.masonryClass" />
	</a>
	<a class="px-1 cursor-pointer group hidden sm:inline-block h-8" :title="$t('gallery.layout.grid')" @click="layout = 'grid'">
		<MiniIcon icon="grid" fill="fill-transparent" :class="layoutStore.gridClass" />
	</a>
</template>
<script setup lang="ts">
import MiniIcon from "@/components/icons/MiniIcon.vue";
import { useLayoutStore } from "@/stores/LayoutState";
import { usePhotosStore, type PhotoRatingFilter } from "@/stores/PhotosState";
import { storeToRefs } from "pinia";
import { ref, type Ref } from "vue";

const layoutStore = useLayoutStore();
const photosStore = usePhotosStore();
const { layout } = storeToRefs(layoutStore);

const starButtons: Ref<HTMLButtonElement[]> = ref([]);

/**
 * Handle star click - toggle filter on/off
 */
function handleStarClick(star: number): void {
	const current = photosStore.photoRatingFilter;
	if (current === star) {
		photosStore.setPhotoRatingFilter(null);
	} else {
		photosStore.setPhotoRatingFilter(star as PhotoRatingFilter);
	}
}

/**
 * Handle keyboard navigation within star filter group
 */
function handleKeyDown(event: KeyboardEvent, star: number): void {
	if (event.key === "ArrowRight" && star < 5) {
		event.preventDefault();
		starButtons.value[star]?.focus();
	} else if (event.key === "ArrowLeft" && star > 1) {
		event.preventDefault();
		starButtons.value[star - 2]?.focus();
	} else if (event.key === "Enter" || event.key === " ") {
		event.preventDefault();
		handleStarClick(star);
	}
}

/**
 * Get the icon class for a star based on the current filter state
 */
function starIconClass(star: number): string {
	const filter = photosStore.photoRatingFilter;
	const filled = typeof filter === "number" && star <= filter;
	return filled ? "pi pi-star-fill text-yellow-500 text-base" : "pi pi-star text-neutral-400 dark:text-neutral-500 hover:text-yellow-400 text-base";
}
</script>
