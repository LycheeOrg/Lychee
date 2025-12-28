import PhotoService from "@/services/photo-service";
import { PhotoStore } from "@/stores/PhotoState";
import { usePhotosStore } from "@/stores/PhotosState";
import { UserStore } from "@/stores/UserState";
import { trans } from "laravel-vue-i18n";
import { ToastServiceMethods } from "primevue/toastservice";
import { ref } from "vue";

export function useRating(photoStore: PhotoStore, toast: ToastServiceMethods, userStore: UserStore) {
	const photosStore = usePhotosStore();
	const loading = ref(false);
	const hoverRating = ref<number | null>(null);

	function handleRatingClick(photoId: string, rating: 0 | 1 | 2 | 3 | 4 | 5) {
		if (loading.value) {
			return;
		}
		if (!userStore.user?.id) {
			return;
		}
		if (!photoStore.photo?.rating) {
			return;
		}

		loading.value = true;

		PhotoService.setRating(photoId, rating)
			.then((response) => {
				// Update only the rating field in the current photo store
				if (photoStore.photo !== null && photoStore.photo !== undefined) {
					photoStore.photo.rating = response.data.rating;
				}

				// Update only the rating field in the album list (photosStore) to keep it in sync
				const photoIndex = photosStore.photos.findIndex((p) => p.id === photoId);
				if (photoIndex !== -1) {
					photosStore.photos[photoIndex].rating = response.data.rating;
				}

				// Show success message
				const message = rating === 0 ? "gallery.photo.rating.removed" : "gallery.photo.rating.saved";
				toast.add({
					severity: "success",
					summary: trans("toasts.success"),
					detail: trans(message),
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
					summary: trans("toasts.error"),
					detail: trans(errorMessage),
					life: 5000,
				});
			})
			.finally(() => {
				loading.value = false;
				hoverRating.value = null;
			});
	}

	return {
		hoverRating,
		loading,
		handleRatingClick,
	};
}
