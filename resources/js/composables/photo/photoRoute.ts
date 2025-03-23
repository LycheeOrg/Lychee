import { ALL } from "@/config/constants";
import { TogglablesStateStore } from "@/stores/ModalsState";

export function usePhotoRoute(togglableStore: TogglablesStateStore) {
	function photoRoute(albumId: string | undefined, photoId: string) {
		return { name: togglableStore.isSearchActive ? "search-photo" : "photo", params: { albumid: albumId ?? ALL, photoid: photoId } };
	}

	return { photoRoute };
}
