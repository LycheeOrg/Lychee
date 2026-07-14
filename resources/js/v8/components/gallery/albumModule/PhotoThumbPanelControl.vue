<template>
	<!-- Tag filtering -->
	<div class="inline-flex items-center gap-0.5 pr-2 mr-2 border-r border-accented h-8" v-if="albumStore.modelAlbum">
		<UButton icon="lucide:tag" color="neutral" variant="ghost" class="hover:text-default" @click="modalStore.toggleFilters" />
	</div>
	<!-- Star Rating Filter (visible only when rated photos exist) -->
	<div
		class="inline-flex items-center gap-0.5 pr-2 mr-2 border-r border-accented h-8"
		v-if="lycheeStore.is_se_enabled && (albumsStore.rootRights?.can_highlight || albumStore.album?.rights?.can_edit)"
	>
		<UTooltip :text="$t('gallery.album.show_highlighted')">
			<UButton
				icon="lucide:flag"
				:ui="{ leadingIcon: photosStore.photoRatingFilter === 'highlighted' ? FILL_OVERRIDE_CLASS : '' }"
				:label="String(photosStore.highlightedPhotosCount)"
				color="neutral"
				variant="ghost"
				class="hover:text-default"
				@click="handleShowHighlightedClick"
			/>
		</UTooltip>
	</div>
	<div
		v-if="photosStore.hasRatedPhotos"
		role="group"
		:aria-label="$t('gallery.filter.by_rating')"
		class="inline-flex items-center gap-0.5 pr-2 mr-2 border-r border-accented h-8"
	>
		<button
			v-for="star in 5"
			:key="star"
			ref="starButtons"
			type="button"
			:aria-label="$t('gallery.filter.n_stars_or_higher', { n: star.toString() })"
			:aria-pressed="photosStore.photoRatingFilter === star"
			class="p-1 cursor-pointer transition-transform duration-150 hover:scale-110 rounded outline-primary/25 focus-visible:outline-3 focus-visible:ring-primary"
			@click="handleStarClick(star)"
			@keydown="handleKeyDown($event, star)"
		>
			<UIcon name="lucide:star" :class="starIconClass(star)" />
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
	<a class="px-1 cursor-pointer group hidden sm:inline-block h-8" :title="$t('gallery.layout.list')" @click="layout = 'list'">
		<MiniIcon icon="list" fill="" :class="layoutStore.listClass" />
	</a>
</template>
<script setup lang="ts">
import MiniIcon from "@/v8/components/icons/MiniIcon.vue";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useAlbumStore } from "@/stores/AlbumState";
import { useLayoutStore } from "@/stores/LayoutState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { usePhotosStore, type PhotoRatingFilter } from "@/stores/PhotosState";
import { storeToRefs } from "pinia";
import { ref, type Ref } from "vue";
import { FILL_OVERRIDE_CLASS } from "@/v8/icons";

const layoutStore = useLayoutStore();
const photosStore = usePhotosStore();
const lycheeStore = useLycheeStateStore();
const albumsStore = useAlbumsStore();
const albumStore = useAlbumStore();
const { layout } = storeToRefs(layoutStore);
const modalStore = useTogglablesStateStore();
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

function handleShowHighlightedClick(): void {
	if (photosStore.photoRatingFilter === "highlighted") {
		photosStore.setPhotoRatingFilter(null);
	} else {
		photosStore.setPhotoRatingFilter("highlighted");
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

function starIconClass(star: number): string {
	const filter = photosStore.photoRatingFilter;
	const filled = typeof filter === "number" && star <= filter;
	return filled ? `text-yellow-500 text-base ${FILL_OVERRIDE_CLASS}` : "text-neutral-400 dark:text-neutral-500 hover:text-yellow-400 text-base";
}
</script>
