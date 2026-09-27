import { ref } from "vue";
import { AxiosError } from "axios";
import AlbumService from "@/services/album-service";
import GalleryPasswordService from "@/services/gallery-password-service";

export type GalleryUnlockError = "invalid_password" | "too_many_attempts" | undefined;

/**
 * Form logic of the gallery password screen, shared by v7 and v8.
 *
 * On success the page is reloaded: the unlock cookie is then sent with every request,
 * responses cached while locked are dropped, and the visitor lands on the page they asked for.
 */
export function useGalleryUnlock() {
	const password = ref<string>("");
	const error = ref<GalleryUnlockError>(undefined);
	const is_submitting = ref(false);

	function toError(e: unknown): GalleryUnlockError {
		const status = e instanceof AxiosError ? e.response?.status : undefined;
		return status === 429 ? "too_many_attempts" : "invalid_password";
	}

	function unlock() {
		if (password.value === "" || is_submitting.value) {
			return;
		}

		is_submitting.value = true;
		error.value = undefined;
		GalleryPasswordService.unlock(password.value)
			.then(() => {
				AlbumService.clearCache();
				window.location.reload();
			})
			.catch((e: unknown) => {
				error.value = toError(e);
				is_submitting.value = false;
			});
	}

	return { password, error, is_submitting, unlock };
}
