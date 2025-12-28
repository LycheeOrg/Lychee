<template>
	<div
		v-if="isRatingEnabled && !are_details_open && displayedRating > 0"
		:class="{
			'absolute bottom-0 w-full sm:w-1/2 left-1/2 -translate-x-1/2 z-20 sm:h-1/8 h-14': true,
			'lg:hover:opacity-100 transition-opacity duration-500 ease-in-out': isHoverMode && !isTouchDevice(),
			'opacity-50 lg:opacity-20': isHoverMode && !isTouchDevice() && !isFullTransparency,
			'opacity-75': isHoverMode && isTouchDevice() && !isFullTransparency,
			'opacity-0': isHoverMode && isFullTransparency,
			hidden: is_slideshow_active,
		}"
	>
		<div class="absolute left-1/2 -translate-x-1/2 bottom-7 flex items-center gap-1 text-shadow">
			<StarRow :rating="displayedRating" size="large" />
		</div>
	</div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { usePhotoStore } from "@/stores/PhotoState";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { storeToRefs } from "pinia";
import StarRow from "@/components/icons/StarRow.vue";

const photoStore = usePhotoStore();
const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();

const { is_slideshow_active, are_details_open } = storeToRefs(togglableStore);

// Compute which rating should be shown on thumbnails
const displayedRating = computed(() => {
	if (lycheeStore.is_rating_show_avg_in_photo_view_enabled) {
		return photoStore.photo?.rating?.rating_avg ?? 0;
	}
	return photoStore.photo?.rating?.rating_user ?? 0;
});

// Compute if rating should be shown in photo view
const isRatingEnabled = computed(() => {
	if (photoStore.photo?.rating === null) {
		return false;
	}
	if (lycheeStore.rating_photo_view_mode === "never") {
		return false;
	}
	return true;
});

const isHoverMode = computed(() => lycheeStore.rating_photo_view_mode === "hover");
const isFullTransparency = computed(() => {
	if (isTouchDevice()) {
		return lycheeStore.is_mobile_dock_full_transparency_enabled;
	}
	return lycheeStore.is_desktop_dock_full_transparency_enabled;
});
</script>
