import PhotoService from "@/services/photo-service";
import { PhotoStore } from "@/stores/PhotoState";
import { UserStore } from "@/stores/UserState";
import { ToastServiceMethods } from "primevue/toastservice";
import { ref } from "vue";

export function useRating(photoStore: PhotoStore, toast: ToastServiceMethods, userStore: UserStore) {
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
				if (photoStore.photo !== null && photoStore.photo !== undefined) {
					// Update photo store with new photo data (includes updated rating)
					photoStore.photo = response.data;
				}

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

	return {
		hoverRating,
		loading,
		handleRatingClick,
	};
}
