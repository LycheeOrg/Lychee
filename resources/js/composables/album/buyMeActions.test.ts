/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

// @vitest-environment jsdom
import { describe, expect, it, vi } from "vite-plus/test";
import { useBuyMeActions } from "@/composables/album/buyMeActions";
import type { AlbumStore } from "@/stores/AlbumState";
import type { CatalogStore } from "@/stores/CatalogState";
import type { OrderManagementStateStore } from "@/stores/OrderManagement";
import type { PhotosStore } from "@/stores/PhotosState";

type Variant = { url: string } | null;

const PRICES = [
	{ size_variant: "original", license_type: "personal", price: "1" },
	{ size_variant: "medium", license_type: "personal", price: "2" },
];

function setup(variants: Record<string, Variant>) {
	const photo = {
		id: "p1",
		title: "p1",
		size_variants: { original: null, thumb: null, medium: null, medium2x: null, ...variants } as Record<string, Variant>,
	};
	const albumStore = {
		modelAlbum: { id: "a1" },
		loadPhotoDetails: vi.fn(async () => {
			photo.size_variants = { ...photo.size_variants, original: { url: "o" }, thumb: { url: "t" }, medium: { url: "m" } };
			return true;
		}),
	};
	const orderManagement = { load: vi.fn(async () => undefined), order: { items: [] }, removeItem: vi.fn(), addPhoto: vi.fn(async () => undefined) };
	const catalogStore = { catalog: { album_purchasable: { prices: PRICES, purchasable_id: undefined } } };
	const actions = useBuyMeActions(
		albumStore as unknown as AlbumStore,
		{ photos: [photo] } as unknown as PhotosStore,
		orderManagement as unknown as OrderManagementStateStore,
		catalogStore as unknown as CatalogStore,
		{ add: vi.fn() },
	);
	return { albumStore, actions };
}

describe("toggleBuyMe", () => {
	it("loads the details of a struct-of-arrays photo before offering its prices (S-105-13)", async () => {
		const { albumStore, actions } = setup({});
		await actions.toggleBuyMe("p1");
		expect(albumStore.loadPhotoDetails).toHaveBeenCalledWith(["p1"]);
		expect(actions.prices.value.map((price) => price.size_variant)).toEqual(["original", "medium"]);
	});

	it("asks no details for a photo that has its size variants", async () => {
		const { albumStore, actions } = setup({ original: { url: "o" }, thumb: { url: "t" }, medium: { url: "m" } });
		await actions.toggleBuyMe("p1");
		expect(albumStore.loadPhotoDetails).not.toHaveBeenCalled();
		expect(actions.prices.value.map((price) => price.size_variant)).toEqual(["original", "medium"]);
	});
});
