/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it, vi } from "vite-plus/test";
import { mount } from "@vue/test-utils";
import GallerySizesControls from "@/v8/components/gallerySizes/GallerySizesControls.vue";
import { DEFAULT_GALLERY_SIZES, GALLERY_SIZE_KEYS, type GallerySizeKey } from "@/v8/utils/gallerySizes";

vi.mock("laravel-vue-i18n", () => ({ trans: (key: string) => key }));

// jsdom has no ResizeObserver; Nuxt UI form components observe their size.
globalThis.ResizeObserver ??= class {
	observe(): void {}
	unobserve(): void {}
	disconnect(): void {}
} as unknown as typeof ResizeObserver;

function mountControls(props: Record<string, unknown> = {}) {
	return mount(GallerySizesControls, {
		props: {
			sizes: { ...DEFAULT_GALLERY_SIZES },
			editable: [...GALLERY_SIZE_KEYS] as GallerySizeKey[],
			layout: "justified",
			plain: true,
			...props,
		},
		global: {
			mocks: { $t: (key: string) => key },
			stubs: {
				UInputNumber: { props: ["id"], template: '<input :id="id" />' },
				USlider: { template: "<div />" },
				USelect: { props: ["id"], template: '<select :id="id" />' },
				UFieldGroup: { template: "<div><slot /></div>" },
				UButton: { props: ["label"], template: "<button>{{ label }}</button>" },
			},
		},
	});
}

const label = (wrapper: ReturnType<typeof mountControls>, key: string) => wrapper.find(`[for="gallery-sizes-${key}"]`);

describe("GallerySizesControls", () => {
	it("shows only the sections the page has (FR-110-14)", () => {
		const albumsOnly = mountControls({ showPhotos: false });
		expect(label(albumsOnly, "album_thumb_min_width").exists()).toBe(true);
		expect(label(albumsOnly, "photo_layout_gap").exists()).toBe(false);
		expect(albumsOnly.text()).not.toContain("gallery_sizes.section_photos");

		const photosOnly = mountControls({ showAlbums: false });
		expect(label(photosOnly, "album_thumb_min_width").exists()).toBe(false);
		expect(label(photosOnly, "photo_layout_gap").exists()).toBe(true);
		expect(photosOnly.text()).not.toContain("gallery_sizes.section_albums");
	});

	it("shows both sections by default (preview page)", () => {
		const both = mountControls();
		expect(label(both, "album_thumb_min_width").exists()).toBe(true);
		expect(label(both, "photo_layout_gap").exists()).toBe(true);
	});

	it.each(["album_thumb_min_width", "album_thumb_gap", "photo_layout_gap", "photo_layout_justified_row_height", "aspect-ratio"])(
		"double-clicking the %s label asks to reset it to the saved value",
		async (key) => {
			const wrapper = mountControls();
			await label(wrapper, key).trigger("dblclick");
			const expected = key === "aspect-ratio" ? "default_album_thumb_aspect_ratio" : key;
			expect(wrapper.emitted("reset")).toEqual([[expected]]);
		},
	);

	it("switches to the layout of a reset layout-specific setting", async () => {
		const wrapper = mountControls({ layout: "justified" });
		await label(wrapper, "photo_layout_masonry_column_width").trigger("dblclick");
		expect(wrapper.emitted("reset")).toEqual([["photo_layout_masonry_column_width"]]);
		expect(wrapper.emitted("update:layout")).toEqual([["masonry"]]);
	});

	it("explains the double-click on each resettable label", () => {
		const wrapper = mountControls();
		expect(label(wrapper, "album_thumb_min_width").attributes("title")).toBe("gallery_sizes.reset_hint");
	});

	it("mutes the settings of the other layouts, never the shared gap", () => {
		const wrapper = mountControls({ layout: "justified" });
		expect(label(wrapper, "photo_layout_justified_row_height").classes()).not.toContain("text-muted");
		expect(label(wrapper, "photo_layout_grid_column_width").classes()).toContain("text-muted");
		expect(label(wrapper, "photo_layout_gap").classes()).not.toContain("text-muted");
	});
});
