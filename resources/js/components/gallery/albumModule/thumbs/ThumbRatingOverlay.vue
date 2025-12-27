<template>
	<div
		v-if="isRatingEnabled && (currentUserRating !== null || hoverRating !== null)"
		:class="{
			'absolute top-0 ltr:right-0 rtl:left-0 m-2 px-2 py-1 bg-black/60 backdrop-blur-sm rounded flex items-center gap-0.5': true,
			'opacity-0 group-hover:opacity-100 transition-opacity ease-out': !compact && lycheeStore.rating_album_view_mode === 'hover',
		}"
	>
		<i
			v-for="rating in [1, 2, 3, 4, 5]"
			:key="`thumb-star-${rating}`"
			:class="{
				'pi pi-star-fill text-yellow-500': rating <= (hoverRating || currentUserRating || 0),
				'pi pi-star text-gray-400': rating > (hoverRating || currentUserRating || 0),
			}"
			class="text-xs"
		/>
	</div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { useLycheeStateStore } from "@/stores/LycheeState";

const lycheeStore = useLycheeStateStore();
const hoverRating = ref<number | null>(null);

const props = defineProps<{
	currentUserRating: number | null;
	compact?: boolean;
}>();

// Compute if rating should be shown on thumbnails
const isRatingEnabled = computed(() => {
	if (!lycheeStore.is_ratings_enabled || !lycheeStore.is_rating_show_avg_in_album_view_enabled) {
		return false;
	}

	// Check view mode setting
	const mode = lycheeStore.rating_album_view_mode;
	if (mode === "never") {
		return false;
	}

	// For "always" mode or compact mode, always show (no hover needed)
	// For "hover" mode in non-compact, the CSS handles the hover transition
	return true;
});
</script>
