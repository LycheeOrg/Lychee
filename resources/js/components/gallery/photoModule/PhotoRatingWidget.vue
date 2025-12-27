<template>
	<div v-if="isRatingEnabled" class="mt-4">
		<h2 class="text-muted-color-emphasis text-base font-bold mb-2">{{ $t("gallery.photo.rating.header") }}</h2>

		<!-- Average rating display (when metrics enabled) -->
		<div v-if="isMetricsEnabled && statistics && statistics.rating_count > 0" class="flex items-center gap-2 mb-3">
			<div class="flex items-center">
				<i
					v-for="index in 5"
					:key="`avg-star-${index}`"
					:class="{
						'pi pi-star-fill text-yellow-500': index <= Math.floor(averageRating),
						'pi pi-star-half-fill text-yellow-500':
							index === Math.ceil(averageRating) && averageRating % 1 >= 0.25 && averageRating % 1 < 0.75,
						'pi pi-star text-gray-400':
							index > Math.ceil(averageRating) || (index === Math.ceil(averageRating) && averageRating % 1 < 0.25),
					}"
					class="text-lg"
				/>
			</div>
			<span class="text-sm text-muted-color">
				{{ averageRating.toFixed(1) }} ({{ statistics.rating_count }}
				{{ statistics.rating_count === 1 ? $t("gallery.photo.rating.rating") : $t("gallery.photo.rating.ratings") }})
			</span>
		</div>

		<!-- Your rating section -->
		<div class="mb-2">
			<span class="text-sm text-muted-color-emphasis">{{ $t("gallery.photo.rating.your_rating") }}:</span>
		</div>

		<!-- Rating buttons -->
		<div class="flex items-center gap-1">
			<!-- Remove rating button (0) -->
			<button
				:disabled="loading || currentUserRating === null"
				:class="{
					'px-2 py-1 text-sm rounded transition-colors': true,
					'bg-red-600 hover:bg-red-700 text-white': currentUserRating !== null && !loading,
					'bg-gray-500 text-gray-300 cursor-not-allowed': loading || currentUserRating === null,
				}"
				@click="handleRatingClick(0)"
			>
				×
			</button>

			<!-- Star rating buttons (1-5) -->
			<button
				v-for="rating in [1, 2, 3, 4, 5]"
				:key="`rate-${rating}`"
				:disabled="loading"
				:class="{
					'px-2 py-1.5 text-lg transition-colors rounded': true,
					'cursor-pointer': !loading,
					'cursor-not-allowed opacity-50': loading,
				}"
				@mouseenter="handleMouseEnter(rating)"
				@mouseleave="handleMouseLeave()"
				@click="handleRatingClick(rating as 1 | 2 | 3 | 4 | 5)"
			>
				<i
					:class="{
						'pi pi-star-fill text-yellow-500': rating <= (hoverRating || currentUserRating || 0),
						'pi pi-star text-gray-400': rating > (hoverRating || currentUserRating || 0),
					}"
				/>
			</button>
		</div>

		<!-- Loading indicator -->
		<div v-if="loading" class="mt-2 text-sm text-muted-color">
			<i class="pi pi-spin pi-spinner mr-1" />
			{{ $t("gallery.photo.rating.saving") }}
		</div>
	</div>
</template>

<script setup lang="ts">
import { ref, computed } from "vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { usePhotoStore } from "@/stores/PhotoState";
import { useToast } from "primevue/usetoast";
import PhotoService from "@/services/photo-service";

const lycheeStore = useLycheeStateStore();
const photoStore = usePhotoStore();
const toast = useToast();

const loading = ref(false);
const hoverRating = ref<number | null>(null);

const props = defineProps<{
	photoId: string;
	statistics: App.Http.Resources.Models.PhotoStatisticsResource | null;
	currentUserRating: number | null;
}>();

// Compute if rating is enabled based on config
const isRatingEnabled = computed(() => lycheeStore.is_ratings_enabled);

// Compute if metrics and average should be shown in details
const isMetricsEnabled = computed(() => lycheeStore.is_rating_show_avg_in_details_enabled && lycheeStore.is_live_metrics_enabled);

// Compute average rating from statistics
const averageRating = computed(() => {
	if (!props.statistics || props.statistics.rating_count === 0) {
		return 0;
	}
	return props.statistics.rating_avg || 0;
});

function handleMouseEnter(rating: number) {
	if (!loading.value) {
		hoverRating.value = rating;
	}
}

function handleMouseLeave() {
	hoverRating.value = null;
}

function handleRatingClick(rating: 0 | 1 | 2 | 3 | 4 | 5) {
	if (loading.value) {
		return;
	}

	loading.value = true;

	PhotoService.setRating(props.photoId, rating)
		.then((response) => {
			// Update photo store with new photo data (includes updated rating)
			photoStore.photo = response.data;

			// Show success message
			const message = rating === 0 ? "gallery.photo.rating.removed" : "gallery.photo.rating.saved";
			toast.add({
				severity: "success",
				summary: "Success",
				detail: message,
				life: 3000,
			});
		})
		.catch((error) => {
			console.error("Failed to save rating:", error);

			// Show error toast
			let errorMessage = "gallery.photo.rating.error";

			if (error.response?.status === 401) {
				errorMessage = "gallery.photo.rating.error_unauthorized";
			} else if (error.response?.status === 403) {
				errorMessage = "gallery.photo.rating.error_forbidden";
			} else if (error.response?.status === 404) {
				errorMessage = "gallery.photo.rating.error_not_found";
			}

			toast.add({
				severity: "error",
				summary: "Error",
				detail: errorMessage,
				life: 5000,
			});
		})
		.finally(() => {
			loading.value = false;
			hoverRating.value = null;
		});
}
</script>
