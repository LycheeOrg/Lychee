<template>
	<div
		v-if="isRatingEnabled && !are_details_open"
		:class="{
			'group absolute bottom-0 w-full sm:w-1/2 left-1/2 -translate-x-1/2 z-20 sm:h-1/8 h-14': true,
			'opacity-50 lg:opacity-20': isHoverMode && !isTouchDevice() && !isFullTransparency,
			'hover:opacity-100 transition-opacity duration-500 ease-in-out': isHoverMode && !isTouchDevice(),
			'opacity-75': isHoverMode && isTouchDevice() && !isFullTransparency,
			'opacity-0': isHoverMode && isFullTransparency,
			hidden: is_slideshow_active,
		}"
	>
		<div v-if="displayedRating > 0" class="absolute left-1/2 -translate-x-1/2 bottom-7 text-shadow group-hover:opacity-0">
			<StarRow :rating="displayedRating" size="large" />
		</div>
		<div
			v-if="userStore.user !== undefined && photoStore?.photo && photoStore?.photo.rating"
			class="absolute left-1/2 -translate-x-1/2 bottom-7 text-shadow"
		>
			<!-- Star rating buttons (1-5) -->
			<div class="flex items-center h-6 gap-0.5">
				<button
					v-for="rating in [1, 2, 3, 4, 5]"
					:key="`rate-${rating}`"
					:disabled="loading"
					:class="{
						'w-6 h-6 transition-colors rounded block': true,
						'cursor-pointer': !loading,
						'cursor-not-allowed opacity-50': loading,
					}"
					@mouseenter="handleMouseEnter(rating)"
					@mouseleave="handleMouseLeave()"
					@click="handleRatingClick(photoStore?.photo.id, rating as 1 | 2 | 3 | 4 | 5)"
				>
					<i
						:class="{
							'text-xl pi pi-star-fill text-amber-500': rating <= (hoverRating ?? photoStore?.photo.rating.rating_user),
							'text-xl pi pi-star text-muted-color': rating > (hoverRating ?? photoStore?.photo.rating.rating_user),
						}"
					/>
				</button>
			</div>
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
import { useUserStore } from "@/stores/UserState";
import { useToast } from "primevue/usetoast";
import { useRating } from "@/composables/photo/useRating";

const photoStore = usePhotoStore();
const lycheeStore = useLycheeStateStore();
const togglableStore = useTogglablesStateStore();
const userStore = useUserStore();
const toast = useToast();

const { is_slideshow_active, are_details_open } = storeToRefs(togglableStore);

const { hoverRating, loading, handleRatingClick } = useRating(photoStore, toast, userStore);

function handleMouseEnter(rating: number) {
	if (!loading.value) {
		hoverRating.value = rating;
	}
}

function handleMouseLeave() {
	hoverRating.value = null;
}

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
