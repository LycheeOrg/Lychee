<template>
	<div
		v-if="displayedRating > 0"
		:class="{
			'absolute bottom-0 ltr:right-0 rtl:left-0 m-2 px-2 py-1 filter-shadow  rounded flex items-center gap-0.5': true,
			'opacity-0 group-hover:opacity-100 transition-opacity ease-out': lycheeStore.rating_album_view_mode === 'hover',
		}"
	>
		<StarRow :rating="displayedRating" size="small" />
	</div>
</template>

<script setup lang="ts">
import { computed } from "vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import StarRow from "@/components/icons/StarRow.vue";

const lycheeStore = useLycheeStateStore();

const props = defineProps<{
	rating: App.Http.Resources.Models.PhotoRatingResource;
	compact?: boolean;
}>();

// Compute which rating should be shown on thumbnails
const displayedRating = computed(() => {
	if (lycheeStore.is_rating_show_avg_in_album_view_enabled) {
		return props.rating.rating_avg;
	}
	return props.rating.rating_user;
});
</script>
