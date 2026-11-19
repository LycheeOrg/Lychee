/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

// @vitest-environment jsdom
import { beforeEach, describe, expect, it, vi } from "vite-plus/test";
import { mount } from "@vue/test-utils";
import { createPinia, setActivePinia } from "pinia";
import AlbumCardV3 from "@/v8/components/gallery/flowModule/AlbumCardV3.vue";
import { useFlowStateStore } from "@/stores/FlowState";
import type { AdaptedFlowTile } from "@/v8/utils/adaptFlowTile";
import type { AdaptedPhotoTile } from "@/v8/utils/adaptPhotoTile";

vi.mock("vue-router", () => ({ useRouter: () => ({ push: vi.fn() }), useRoute: () => ({ params: {}, query: {} }) }));

/**
 * Feature 109 (FR-109-07): the v3 Flow card header renders like v2
 * `HeaderImage.vue` — `fit` shows the whole photo over a blurred copy,
 * `cover` crops it, and "show more" drops the fixed header height.
 */
const ALBUM: AdaptedFlowTile = {
	id: "a1",
	title: "Trip",
	description: "",
	coverId: "c1",
	ownerName: null,
	isNsfw: false,
	numPhotos: 3,
	numChildren: 0,
	minMaxText: null,
	publishedCreatedAt: "",
	diffPublishedCreatedAt: "",
	statistics: null,
};

function config(overrides: Partial<App.Http.Resources.Flow.InitResource> = {}): App.Http.Resources.Flow.InitResource {
	return {
		is_mod_flow_enabled: true,
		is_open_album_on_click: false,
		is_display_open_album_button: false,
		is_highlight_first_picture: false,
		is_image_header_enabled: true,
		image_header_cover: "cover",
		image_header_height: 24,
		is_carousel_enabled: false,
		carousel_height: 6,
		is_blur_nsfw_enabled: true,
		is_compact_mode_enabled: true,
		...overrides,
	} as App.Http.Resources.Flow.InitResource;
}

function mountCard(cfg: App.Http.Resources.Flow.InitResource, album: AdaptedFlowTile = ALBUM) {
	return mount(AlbumCardV3, {
		props: { album, config: cfg },
		global: {
			mocks: { $t: (key: string) => key },
			stubs: {
				AlbumCardSkeletonV3: { template: '<div data-skeleton="true" />' },
				Thumb: { props: ["albumId", "photoId", "type"], template: '<img :data-photo-id="photoId" :data-type="type" />' },
				UButton: { template: "<button><slot /></button>" },
				UTooltip: { template: "<span><slot /></span>" },
				Tooltip: { template: "<span><slot /></span>" },
				UIcon: true,
				MiniIcon: true,
				AlbumStatistics: true,
			},
		},
	});
}

/** Puts `count` preview photos `p0` … in the store for album `a1`. */
function setPreview(count: number, state: "loading" | "loaded" = "loaded") {
	const flowState = useFlowStateStore();
	flowState.cardLoadStateV3 = { a1: state };
	flowState.cardPhotosV3 = { a1: Array.from({ length: count }, (_, i) => ({ id: `p${i}` }) as AdaptedPhotoTile) };
}

function headerImages(wrapper: ReturnType<typeof mountCard>) {
	return wrapper.findAll('[data-photo-id="c1"]');
}

beforeEach(() => {
	setActivePinia(createPinia());
	const flowState = useFlowStateStore();
	flowState.cardLoadStateV3 = { a1: "loaded" };
	flowState.cardPhotosV3 = { a1: [{ id: "p1" } as AdaptedPhotoTile, { id: "p2" } as AdaptedPhotoTile] };
	vi.spyOn(flowState, "requestCardPhotos").mockResolvedValue(undefined);
});

describe("AlbumCardV3 header", () => {
	it("crops the header photo in cover mode", () => {
		const wrapper = mountCard(config({ image_header_cover: "cover" }));

		const images = headerImages(wrapper);
		expect(images).toHaveLength(1);
		expect(images[0].classes()).toContain("object-cover");
		expect(images[0].classes()).not.toContain("object-contain");
		// Q-109-06 A: sharp at the 24 rem default header height on 1x and 2x screens.
		expect(images[0].attributes("data-type")).toBe("small2x");
	});

	it("shows the whole header photo over a blurred copy in fit mode", () => {
		const wrapper = mountCard(config({ image_header_cover: "fit" }));

		const images = headerImages(wrapper);
		expect(images).toHaveLength(2);
		const [background, foreground] = images;
		expect(background.classes()).toContain("object-cover");
		expect(foreground.classes()).toContain("object-contain");
		expect(foreground.attributes("data-type")).toBe("small2x");
		expect(background.attributes("data-type")).toBe("thumb");
		expect(wrapper.find(".blur-3xl").exists()).toBe(true);
	});

	it.each(["cover", "fit"] as const)("shows the header photo at its natural height once show more is open (%s)", async (mode) => {
		const wrapper = mountCard(config({ image_header_cover: mode }));
		expect(headerImages(wrapper)[0].element.parentElement?.className).toContain("h-(--header-height)");

		const showMore = wrapper.findAll("button").find((button) => button.text() === "flow.show_more");
		expect(showMore).toBeDefined();
		await showMore!.trigger("click");

		const images = headerImages(wrapper);
		expect(images).toHaveLength(1);
		expect(images[0].classes()).toContain("w-full");
		expect(images[0].classes()).not.toContain("h-full");
		expect(images[0].attributes("data-type")).toBe("small2x");
		expect(images[0].element.parentElement?.className).not.toContain("h-(--header-height)");
	});
});

/**
 * Feature 109 (FR-109-09): with the image header off, the card shows v2
 * `TopImages.vue`'s grid — up to 5 photos, a "+N" tile, one full-height row
 * below 4 photos.
 */
describe("AlbumCardV3 top-images grid", () => {
	const headerOff = () => config({ is_image_header_enabled: false, is_carousel_enabled: true });

	it("shows 5 photos in wrapped rows and a +N tile for a larger album", () => {
		setPreview(8);
		const wrapper = mountCard(headerOff(), { ...ALBUM, numPhotos: 20 });

		const thumbs = wrapper.findAll('img[data-photo-id^="p"]');
		expect(thumbs.map((t) => t.attributes("data-photo-id"))).toEqual(["p0", "p1", "p2", "p3", "p4"]);
		expect(thumbs.every((t) => t.attributes("data-type") === "small")).toBe(true);
		const grid = thumbs[0].element.parentElement!.parentElement!;
		expect(grid.className).toContain("flex-wrap");
		expect(thumbs[0].element.parentElement!.className).toContain("h-(--top-images-height)");
		expect(grid.textContent).toContain("15");
		expect(wrapper.find('[data-photo-id="c1"]').exists()).toBe(false);
	});

	it("shows one full-height row without +N below 4 photos", () => {
		setPreview(3);
		const wrapper = mountCard(headerOff());

		const thumbs = wrapper.findAll('img[data-photo-id^="p"]');
		expect(thumbs).toHaveLength(3);
		const grid = thumbs[0].element.parentElement!.parentElement!;
		expect(grid.className).not.toContain("flex-wrap");
		expect(thumbs[0].element.parentElement!.className).toContain("h-(--header-height)");
		expect(grid.textContent?.trim()).toBe("");
	});

	it("opens the clicked photo", async () => {
		setPreview(3);
		const wrapper = mountCard(headerOff());

		await wrapper.find('img[data-photo-id="p2"]').trigger("click");
		expect(wrapper.emitted("setSelection")).toEqual([["a1", "p2"]]);
	});

	it("shows the skeleton while the preview loads", () => {
		setPreview(0, "loading");
		const wrapper = mountCard(headerOff());

		expect(wrapper.find("[data-skeleton]").exists()).toBe(true);
	});
});
