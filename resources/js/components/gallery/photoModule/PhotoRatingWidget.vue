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
					'bg-red-600 hover:bg-red-700 text-white hover:cursor-pointer': props.rating.rating_user > 0 && !loading,
					'bg-gray-500 text-gray-300 cursor-not-allowed': loading || props.rating.rating_user === 0,
				}"
				@click="handleRatingClick(props.photoId, 0)"
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
				@click="handleRatingClick(props.photoId, rating as 1 | 2 | 3 | 4 | 5)"
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
import { useLycheeStateStore } from "@/stores/LycheeState";
import { usePhotoStore } from "@/stores/PhotoState";
import { useToast } from "primevue/usetoast";
import { useUserStore } from "@/stores/UserState";
import StarRow from "@/components/icons/StarRow.vue";
import { useRating } from "@/composables/photo/useRating";

const lycheeStore = useLycheeStateStore();
const photoStore = usePhotoStore();
const userStore = useUserStore();
const toast = useToast();

const { hoverRating, loading, handleRatingClick } = useRating(photoStore, toast, userStore);

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
</script>
