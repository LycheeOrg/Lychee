<template>
	<div
		v-if="isRatingEnabled && (currentUserRating !== null || hoverRating !== null)"
		:class="{
			'absolute top-0 ltr:right-0 rtl:left-0 m-2 px-2 py-1 bg-black/60 backdrop-blur-sm rounded flex items-center gap-0.5': true,
			'opacity-0 group-hover:opacity-100 transition-opacity ease-out': !compact,
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

const hoverRating = ref<number | null>(null);

const props = defineProps<{
	currentUserRating: number | null;
	compact?: boolean;
}>();

// Compute if rating is enabled (placeholder - will be replaced with actual config in I12a)
const isRatingEnabled = computed(() => true);
</script>
