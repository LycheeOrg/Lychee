/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

// @vitest-environment jsdom
import { beforeEach, describe, expect, it, vi } from "vite-plus/test";
import { createPinia, setActivePinia } from "pinia";
import AlbumService from "@/services/album-service";
import ThumbAssetService from "@/services/thumb-asset-service";
import { useClearViewerState } from "@/composables/useClearViewerState";
import { useAlbumStore } from "@/stores/AlbumState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { usePhotoStore } from "@/stores/PhotoState";
import { usePhotosStore } from "@/stores/PhotosState";
import { useGlobalRightsStore } from "@/stores/GlobalRightsState";
import { useUserStore } from "@/stores/UserState";
import { useAlbumListStore } from "@/stores/AlbumListState";

vi.mock("@/services/album-service", () => ({
	default: { clearCache: vi.fn() },
}));

vi.mock("@/services/thumb-asset-service", () => ({
	default: { clear: vi.fn() },
}));

beforeEach(() => {
	setActivePinia(createPinia());
	vi.mocked(AlbumService.clearCache).mockReset();
	vi.mocked(ThumbAssetService.clear).mockReset();
});

describe("useClearViewerState (S-102-15)", () => {
	it("clears every store and cache holding the previous viewer's data", () => {
		const albumStore = useAlbumStore();
		const albumsStore = useAlbumsStore();
		const photoStore = usePhotoStore();
		const photosStore = usePhotosStore();
		const globalRightsStore = useGlobalRightsStore();
		const userStore = useUserStore();
		const albumListStore = useAlbumListStore();
		albumStore.modelAlbum = { id: "album" } as App.Http.Resources.Models.HeadAlbumResource;
		albumsStore.albums = [{ id: "album" } as App.Http.Resources.Models.ThumbAlbumResource];
		photoStore.photoId = "p1";
		photosStore.photos = [{ id: "p1" } as App.Http.Resources.Models.PhotoResource];
		globalRightsStore.rights = {} as App.Http.Resources.Rights.GlobalRightsResource;
		userStore.setUser({ id: 1 } as App.Http.Resources.Models.UserResource);
		albumListStore.isLoaded = true;

		useClearViewerState().clearViewerState();

		expect(albumStore.modelAlbum).toBeUndefined();
		expect(albumsStore.albums).toEqual([]);
		expect(photoStore.photoId).toBeUndefined();
		expect(photosStore.photos).toEqual([]);
		expect(globalRightsStore.rights).toBeUndefined();
		expect(userStore.user).toBeUndefined();
		expect(albumListStore.isLoaded).toBe(false);
		expect(AlbumService.clearCache).toHaveBeenCalledWith();
		expect(ThumbAssetService.clear).toHaveBeenCalledOnce();
	});
});
