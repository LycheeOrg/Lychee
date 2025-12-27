<template>
	<div class="mt-4">
		<h2 class="text-muted-color-emphasis text-base font-bold mb-2">{{ $t("gallery.photo.rating.header") }}</h2>

		<!-- Average rating display -->
		<div v-if="lycheeStore.is_rating_show_avg_in_details_enabled && props.rating.rating_count > 0" class="flex items-center gap-2 mb-3">
			<StarRow :rating="props.rating.rating_avg" size="medium" />
			<span class="text-sm text-muted-color">
				{{ props.rating.rating_avg.toFixed(1) }} ({{ props.rating.rating_count }}
				{{ props.rating.rating_count === 1 ? $t("gallery.photo.rating.rating") : $t("gallery.photo.rating.ratings") }})
			</span>
		</div>

		<!-- Your rating section -->
		<div class="mb-2" v-if="userStore.user !== undefined">
			<span class="text-sm text-muted-color-emphasis">{{ $t("gallery.photo.rating.your_rating") }}:</span>
		</div>

		<!-- Rating buttons -->
		<div class="flex items-center gap-1" v-if="userStore.user !== undefined">
			<!-- Remove rating button (0) -->
			<button
				:disabled="loading || props.rating.rating_user === 0"
				:class="{
					'px-2 py-1 text-sm rounded transition-colors': true,
					'bg-red-600 hover:bg-red-700 text-white': props.rating.rating_user > 0 && !loading,
					'bg-gray-500 text-gray-300 cursor-not-allowed': loading || props.rating.rating_user === 0,
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
						'pi pi-star-fill text-sky-500': rating <= (hoverRating ?? props.rating.rating_user),
						'pi pi-star text-muted-color': rating > (hoverRating ?? props.rating.rating_user),
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
import { ref } from "vue";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { usePhotoStore } from "@/stores/PhotoState";
import { useToast } from "primevue/usetoast";
import PhotoService from "@/services/photo-service";
import { useUserStore } from "@/stores/UserState";
import StarRow from "@/components/icons/StarRow.vue";

const lycheeStore = useLycheeStateStore();
const photoStore = usePhotoStore();
const userStore = useUserStore();
const toast = useToast();

const loading = ref(false);
const hoverRating = ref<number | null>(null);

const props = defineProps<{
	photoId: string;
	rating: App.Http.Resources.Models.PhotoRatingResource;
}>();

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
