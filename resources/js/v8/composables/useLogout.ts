/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import AuthService from "@/services/auth-service";
import AlbumService from "@/services/album-service";
import Constants from "@/services/constants";
import { useGlobalRightsStore } from "@/stores/GlobalRightsState";
import { useUserStore } from "@/stores/UserState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotoStore } from "@/stores/PhotoState";
import { useAlbumListStore } from "@/stores/AlbumListState";

/**
 * Logs the current user out, wipes every identity-dependent store/cache and
 * reloads on /home. Shared by the left menu and the Spotlight palette.
 */
export function useLogout() {
	const globalRightsStore = useGlobalRightsStore();
	const userStore = useUserStore();
	const photosStore = usePhotosStore();
	const albumsStore = useAlbumsStore();
	const albumStore = useAlbumStore();
	const photoStore = usePhotoStore();
	const albumListStore = useAlbumListStore();

	function logout(): Promise<void> {
		return AuthService.logout().then(() => {
			globalRightsStore.reset();
			photoStore.reset();
			photosStore.reset();
			albumsStore.reset();
			albumStore.reset();
			userStore.setUser(undefined);
			AlbumService.clearCache();
			albumListStore.invalidate();
			window.location.href = Constants.BASE_URL + "/home";
		});
	}

	return { logout };
}
