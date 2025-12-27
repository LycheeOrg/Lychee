<template>
	<div
		v-if="isRatingEnabled && photoStore.photo && photoStore.photo.current_user_rating !== null"
		class="absolute top-7 ltr:right-7 rtl:left-7 flex items-center gap-1 text-shadow pointer-events-none"
	>
		<i
			v-for="rating in [1, 2, 3, 4, 5]"
			:key="`overlay-star-${rating}`"
			:class="{
				'pi pi-star-fill text-yellow-500': rating <= (photoStore.photo.current_user_rating || 0),
				'pi pi-star text-gray-400': rating > (photoStore.photo.current_user_rating || 0),
			}"
			class="text-xl"
		/>
	</div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { usePhotoStore } from "@/stores/PhotoState";
import { useLycheeStateStore } from "@/stores/LycheeState";

const photoStore = usePhotoStore();
const lycheeStore = useLycheeStateStore();

// Compute if rating should be shown in photo view
const isRatingEnabled = computed(() => {
	if (!lycheeStore.is_ratings_enabled || !lycheeStore.is_rating_show_avg_in_photo_view_enabled) {
		return false;
	}

	// Check view mode setting
	const mode = lycheeStore.rating_photo_view_mode;
	if (mode === "never") {
		return false;
	}

	// For "always" or "hover" mode, show if user has rated
	// (hover transition would be handled by CSS if needed)
	return true;
});
</script>
