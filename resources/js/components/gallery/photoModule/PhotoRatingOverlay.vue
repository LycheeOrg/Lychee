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

const photoStore = usePhotoStore();

// Compute if rating is enabled (placeholder - will be replaced with actual config in I12a)
const isRatingEnabled = computed(() => true);
</script>
