<template>
	<div class="mt-4">
		<h2 class="text-highlighted text-base font-bold mb-2">{{ $t("gallery.photo.rating.header") }}</h2>

		<!-- Average rating display -->
		<div v-if="lycheeStore.is_rating_show_avg_in_details_enabled && props.rating.rating_count > 0" class="flex items-center gap-2 mb-3">
			<StarRow :rating="props.rating.rating_avg" size="medium" />
			<span class="text-sm text-muted">
				{{ props.rating.rating_avg.toFixed(1) }} ({{ props.rating.rating_count }}
				{{ props.rating.rating_count === 1 ? $t("gallery.photo.rating.rating") : $t("gallery.photo.rating.ratings") }})
			</span>
		</div>

		<!-- Your rating section -->
		<div class="mb-2" v-if="userStore.user !== undefined">
			<span class="text-sm text-highlighted">{{ $t("gallery.photo.rating.your_rating") }}:</span>
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
				<UIcon
					:name="rating <= (hoverRating ?? props.rating.rating_user) ? 'prime:star-fill' : 'prime:star'"
					:class="rating <= (hoverRating ?? props.rating.rating_user) ? 'text-primary' : 'text-muted'"
				/>
			</button>
		</div>

		<!-- Loading indicator -->
		<div v-if="loading" class="mt-2 text-sm text-muted">
			<Spinner class="mr-1" />
			{{ $t("gallery.photo.rating.saving") }}
		</div>
	</div>
</template>

<script setup lang="ts">
import { useLycheeStateStore } from "@/stores/LycheeState";
import { usePhotoStore } from "@/stores/PhotoState";
import { useAppToast } from "@/v8/composables/useAppToast";
import { useUserStore } from "@/stores/UserState";
import StarRow from "@/v8/components/icons/StarRow.vue";
import Spinner from "@/v8/components/Spinner.vue";
import { useRating } from "@/composables/photo/useRating";
import { ref } from "vue";

const lycheeStore = useLycheeStateStore();
const photoStore = usePhotoStore();
const userStore = useUserStore();
const toast = useAppToast();

const { loading, handleRatingClick } = useRating(photoStore, toast, userStore);

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
</script>
