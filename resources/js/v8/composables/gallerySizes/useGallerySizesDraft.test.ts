/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { beforeEach, describe, expect, it, vi } from "vite-plus/test";
import { createPinia, setActivePinia } from "pinia";

const getAll = vi.fn();
const setConfigs = vi.fn();
vi.mock("@/services/settings-service", () => ({
	default: { getAll: (...a: unknown[]) => getAll(...a), setConfigs: (...a: unknown[]) => setConfigs(...a) },
}));

const toastAdd = vi.fn();
vi.mock("@/v8/composables/useAppToast", () => ({ useAppToast: () => ({ add: toastAdd }) }));

vi.mock("laravel-vue-i18n", () => ({ trans: (key: string) => key }));

import { useGallerySizesDraft } from "@/v8/composables/gallerySizes/useGallerySizesDraft";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { useLayoutStore } from "@/stores/LayoutState";
import { useAlbumsStore } from "@/stores/AlbumsState";
import { useAlbumStore } from "@/stores/AlbumState";

function config(key: string, value: string) {
	return {
		key,
		value,
		type: "int",
		is_set: true,
		documentation: "",
		details: "",
		is_expert: false,
		require_se: false,
		order: 0,
		required_keys: [],
	};
}

const SERVER = [
	{
		cat: "Gallery",
		configs: [
			config("album_thumb_min_width", "200"),
			config("album_thumb_gap", "16"),
			config("default_album_thumb_aspect_ratio", "3/2"),
			config("photo_layout_gap", "8"),
			config("photo_layout_justified_row_height", "240"),
			config("photo_layout_masonry_column_width", "280"),
			config("photo_layout_grid_column_width", "260"),
			config("photo_layout_square_column_width", "180"),
		],
	},
];

async function flush() {
	await new Promise((resolve) => setTimeout(resolve, 0));
}

describe("useGallerySizesDraft (FR-110-10, FR-110-11)", () => {
	beforeEach(() => {
		setActivePinia(createPinia());
		getAll.mockReset();
		setConfigs.mockReset();
		toastAdd.mockReset();
		getAll.mockResolvedValue({ data: SERVER });
	});

	it("starts from the session's values before the settings arrive", () => {
		const lychee = useLycheeStateStore();
		lychee.album_thumb_min_width = 222;
		lychee.album_thumb_gap = 7;
		lychee.number_albums_per_row_mobile = 1;
		useLayoutStore().config = {
			photo_layout_gap: 5,
			photo_layout_justified_row_height: 100,
			photo_layout_masonry_column_width: 110,
			photo_layout_grid_column_width: 120,
			photo_layout_square_column_width: 130,
		};
		const draft = useGallerySizesDraft();
		expect(draft.stored.value.album_thumb_min_width).toBe(222);
		expect(draft.stored.value.album_thumb_gap).toBe(7);
		expect(draft.stored.value.number_albums_per_row_mobile).toBe(1);
		expect(draft.stored.value.photo_layout_square_column_width).toBe(130);
		expect(draft.isDirty.value).toBe(false);
	});

	it("re-reads the session's values on resetToSession", () => {
		const draft = useGallerySizesDraft();
		useLycheeStateStore().album_thumb_min_width = 260;
		draft.resetToSession();
		expect(draft.stored.value.album_thumb_min_width).toBe(260);
		expect(draft.draft.value.album_thumb_min_width).toBe(260);
	});

	it("loads the stored values; the SE-only mobile setting stays read-only when absent", async () => {
		const draft = useGallerySizesDraft();
		await draft.load();
		expect(draft.loading.value).toBe(false);
		expect(draft.stored.value.album_thumb_min_width).toBe(200);
		expect(draft.draft.value).toEqual(draft.stored.value);
		expect(draft.editable.value).not.toContain("number_albums_per_row_mobile");
		expect(draft.isDirty.value).toBe(false);
	});

	it("reports a load failure (UI-110-04)", async () => {
		getAll.mockRejectedValue(new Error("boom"));
		const draft = useGallerySizesDraft();
		await draft.load();
		expect(draft.loadError.value).toBe(true);
		expect(toastAdd).toHaveBeenCalledWith(expect.objectContaining({ severity: "error" }));
	});

	it("resets one setting to its saved value, leaving the others", async () => {
		const draft = useGallerySizesDraft();
		await draft.load();
		draft.set("album_thumb_min_width", 300);
		draft.set("photo_layout_gap", 30);
		draft.resetKey("album_thumb_min_width");
		expect(draft.draft.value.album_thumb_min_width).toBe(200);
		expect(draft.draft.value.photo_layout_gap).toBe(30);
		expect(draft.isDirty.value).toBe(true);
	});

	it("clamps edits, tracks dirtiness and reverts (S-110-11)", async () => {
		const draft = useGallerySizesDraft();
		await draft.load();
		draft.set("album_thumb_min_width", 5000);
		expect(draft.draft.value.album_thumb_min_width).toBe(480);
		expect(draft.isDirty.value).toBe(true);
		draft.revert();
		expect(draft.draft.value.album_thumb_min_width).toBe(200);
		expect(draft.isDirty.value).toBe(false);
	});

	it("saves the changed keys only and updates the session stores (S-110-09)", async () => {
		setConfigs.mockResolvedValue({ data: [] });
		const albums = useAlbumsStore();
		albums.rootConfig = { album_thumb_css_aspect_ratio: "aspect-3x2" } as App.Http.Resources.GalleryConfigs.RootConfig;
		const album = useAlbumStore();
		album.config = { album_thumb_css_aspect_ratio: "aspect-3x2" } as App.Http.Resources.GalleryConfigs.AlbumConfig;
		album.modelAlbum = { editable: { aspect_ratio: null } } as unknown as App.Http.Resources.Models.HeadAlbumResource;

		const draft = useGallerySizesDraft();
		await draft.load();
		draft.set("album_thumb_min_width", 250);
		draft.set("album_thumb_gap", 0);
		draft.set("photo_layout_gap", 20);
		draft.set("default_album_thumb_aspect_ratio", "2/3");

		expect(await draft.save()).toBe(true);
		expect(setConfigs).toHaveBeenCalledWith({
			configs: [
				{ key: "album_thumb_min_width", value: "250" },
				{ key: "album_thumb_gap", value: "0" },
				{ key: "default_album_thumb_aspect_ratio", value: "2/3" },
				{ key: "photo_layout_gap", value: "20" },
			],
		});
		expect(draft.isDirty.value).toBe(false);
		expect(useLycheeStateStore().album_thumb_min_width).toBe(250);
		expect(useLycheeStateStore().album_thumb_gap).toBe(0);
		expect(useLayoutStore().config?.photo_layout_gap).toBe(20);
		expect(useLayoutStore().config?.photo_layout_masonry_column_width).toBe(280);
		expect(albums.rootConfig?.album_thumb_css_aspect_ratio).toBe("aspect-2x3");
		expect(album.config?.album_thumb_css_aspect_ratio).toBe("aspect-2x3");
		expect(toastAdd).toHaveBeenCalledWith(expect.objectContaining({ severity: "success" }));
	});

	it("leaves an album's own aspect ratio alone on save", async () => {
		setConfigs.mockResolvedValue({ data: [] });
		const album = useAlbumStore();
		album.config = { album_thumb_css_aspect_ratio: "aspect-video" } as App.Http.Resources.GalleryConfigs.AlbumConfig;
		album.modelAlbum = { editable: { aspect_ratio: "16/9" } } as unknown as App.Http.Resources.Models.HeadAlbumResource;

		const draft = useGallerySizesDraft();
		await draft.load();
		draft.set("default_album_thumb_aspect_ratio", "2/3");
		await draft.save();
		expect(album.config?.album_thumb_css_aspect_ratio).toBe("aspect-video");
	});

	it("keeps the values and the dirty state when the server rejects them (S-110-10)", async () => {
		setConfigs.mockRejectedValue({ response: { data: { message: "invalid value" } } });
		const draft = useGallerySizesDraft();
		await draft.load();
		draft.set("photo_layout_gap", 30);
		expect(await draft.save()).toBe(false);
		await flush();
		expect(draft.isDirty.value).toBe(true);
		expect(draft.draft.value.photo_layout_gap).toBe(30);
		expect(useLayoutStore().config?.photo_layout_gap).not.toBe(30);
		expect(toastAdd).toHaveBeenCalledWith(expect.objectContaining({ severity: "error", detail: "invalid value" }));
	});
});
