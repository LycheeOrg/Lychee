/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import AlbumService from "@/services/album-service";
import ThumbAssetService from "@/services/thumb-asset-service";
import { useGlobalRightsStore } from "@/stores/GlobalRightsState";
import { useUserStore } from "@/stores/UserState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useAlbumStore } from "@/stores/AlbumState";
import { usePhotoStore } from "@/stores/PhotoState";
import { useAlbumListStore } from "@/stores/AlbumListState";

/**
 * Wipes every store and cache holding data loaded for the current viewer, so
 * the next one never reuses it (Feature 102, FR-102-08). Called after every
 * successful login and logout, in v7 and v8.
 */
export function useClearViewerState() {
	const globalRightsStore = useGlobalRightsStore();
	const userStore = useUserStore();
	const photosStore = usePhotosStore();
	const albumsStore = useAlbumsStore();
	const albumStore = useAlbumStore();
	const photoStore = usePhotoStore();
	const albumListStore = useAlbumListStore();

	function clearViewerState(): void {
		globalRightsStore.reset();
		photoStore.reset();
		photosStore.reset();
		albumsStore.reset();
		albumStore.reset();
		userStore.setUser(undefined);
		albumListStore.invalidate();
		AlbumService.clearCache();
		ThumbAssetService.clear();
	}

	return { clearViewerState };
}
