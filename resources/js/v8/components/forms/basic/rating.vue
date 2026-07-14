<template>
	<!-- Star rating buttons (1-5) -->
	<div class="flex items-center h-6 gap-0.5">
		<button
			v-for="rating in [1, 2, 3, 4, 5]"
			:key="`rate-${rating}`"
			:class="{
				'w-6 h-6 transition-colors rounded block': true,
				'cursor-pointer': !loading,
				'cursor-not-allowed opacity-50': loading,
			}"
			@mouseenter="handleMouseEnter(rating)"
			@mouseleave="handleMouseLeave()"
			@click="handleRatingClick(rating as 1 | 2 | 3 | 4 | 5)"
		>
			<UIcon
				name="lucide:star"
				class="text-xl"
				:class="
					rating <= (hoverRating ?? selectedRating ?? 0)
						? `${props.amber ? 'text-amber-500' : 'text-primary'} ${FILL_OVERRIDE_CLASS}`
						: 'text-muted'
				"
			/>
		</button>
	</div>
</template>
<script setup lang="ts">
import { ref, watch } from "vue";
import { FILL_OVERRIDE_CLASS } from "@/v8/icons";

const props = defineProps<{
	loading: boolean;
	selectedRating: number | undefined;
	handleRatingClick: (rating: 1 | 2 | 3 | 4 | 5) => void;
	amber?: boolean;
}>();

const loading = ref(props.loading);
const hoverRating = ref<number | null>(null);
const selectedRating = ref(props.selectedRating);

function handleMouseEnter(rating: number) {
	if (!props.loading) {
		hoverRating.value = rating;
	}
}

function handleMouseLeave() {
	hoverRating.value = null;
}

watch(
	() => [props.loading, props.selectedRating],
	([newLoading, newSelectedRating]) => {
		loading.value = newLoading as boolean;
		selectedRating.value = newSelectedRating as number | undefined;
	},
);
</script>
