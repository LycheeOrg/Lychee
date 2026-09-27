import { computed, onMounted, onUnmounted, watch } from "vue";
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

	// A successful login clears the user (LoginForm) and leaves the reload to the page mounted next.
	// Behind the password screen no gallery page is mounted, so load the user here.
	watch(
		() => isGalleryLocked.value && userStore.user === undefined,
		(needsUser) => {
			if (needsUser) {
				userStore.load().catch(() => {});
			}
		},
	);

	// A logged-in user is never asked for the gallery password, and logging out reloads the page.
	// Drop the lock for good: gallery pages reset the user while re-fetching it on mount, and a
	// lock still set would bring the password screen back, unmount the page, and loop.
	watch(
		() => userStore.isLoggedIn,
		(isLoggedIn) => {
			if (isLoggedIn) {
				lycheeStore.is_gallery_locked = false;
			}
		},
	);

	return { isGalleryLocked };
}
