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
import { useTimelineStore } from "@/stores/TimelineState";

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
		const timelineStore = useTimelineStore();
		albumStore.modelAlbum = { id: "album" } as App.Http.Resources.Models.HeadAlbumResource;
		albumsStore.albums = [{ id: "album" } as App.Http.Resources.Models.ThumbAlbumResource];
		photoStore.photoId = "p1";
		photosStore.photos = [{ id: "p1" } as App.Http.Resources.Models.PhotoResource];
		globalRightsStore.rights = {} as App.Http.Resources.Rights.GlobalRightsResource;
		userStore.setUser({ id: 1 } as App.Http.Resources.Models.UserResource);
		albumListStore.isLoaded = true;
		timelineStore.isTimelineEnabled = true;
		timelineStore.layout = "justified";
		timelineStore.rootConfig = {} as App.Http.Resources.GalleryConfigs.RootConfig;
		timelineStore.rootRights = { can_upload: false } as App.Http.Resources.Rights.RootAlbumRightsResource;
		timelineStore.dates = [{} as App.Http.Resources.Models.Utils.TimelineData];
		timelineStore.bucketsV3 = { bucket_ids: ["2026-10"] } as unknown as typeof timelineStore.bucketsV3;
		timelineStore.tilesV3 = [{ id: "p1" } as App.Http.Resources.Models.PhotoResource];
		timelineStore.loadedBucketsV3 = { "2026-10": true };

		useClearViewerState().clearViewerState();

		expect(albumStore.modelAlbum).toBeUndefined();
		expect(albumsStore.albums).toEqual([]);
		expect(photoStore.photoId).toBeUndefined();
		expect(photosStore.photos).toEqual([]);
		expect(globalRightsStore.rights).toBeUndefined();
		expect(userStore.user).toBeUndefined();
		expect(albumListStore.isLoaded).toBe(false);
		expect(timelineStore.isTimelineEnabled).toBeUndefined();
		expect(timelineStore.layout).toBeUndefined();
		expect(timelineStore.rootConfig).toBeUndefined();
		expect(timelineStore.rootRights).toBeUndefined();
		expect(timelineStore.dates).toEqual([]);
		expect(timelineStore.bucketsV3).toBeUndefined();
		expect(timelineStore.tilesV3).toEqual([]);
		expect(timelineStore.loadedBucketsV3).toEqual({});
		expect(AlbumService.clearCache).toHaveBeenCalledWith();
		expect(ThumbAssetService.clear).toHaveBeenCalledOnce();
	});
});
