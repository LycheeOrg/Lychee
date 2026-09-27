import { computed, onMounted, onUnmounted } from "vue";
import { useRoute } from "vue-router";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useUserStore } from "@/stores/UserState";

export const GALLERY_LOCKED_EVENT = "gallery_locked";

/**
 * Whether the gallery password screen must replace the app.
 *
 * The lock state comes from Gallery::Init, and is raised again whenever an API call
 * answers "Gallery password required" (see axios-config.ts). The login page always
 * stays reachable so account holders can still sign in.
 */
export function useGalleryLock() {
	const lycheeStore = useLycheeStateStore();
	const userStore = useUserStore();
	const route = useRoute();

	function onLocked() {
		lycheeStore.is_gallery_locked = true;
	}

	onMounted(() => {
		window.addEventListener(GALLERY_LOCKED_EVENT, onLocked);
		lycheeStore.load();
	});

	onUnmounted(() => {
		window.removeEventListener(GALLERY_LOCKED_EVENT, onLocked);
	});

	const isGalleryLocked = computed(() => lycheeStore.is_gallery_locked && !userStore.isLoggedIn && route.name !== "login");

	return { isGalleryLocked };
}
