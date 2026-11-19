/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

// @vitest-environment jsdom
import { beforeEach, describe, expect, it, vi } from "vite-plus/test";
import { flushPromises, mount } from "@vue/test-utils";
import Thumb from "@/v8/components/thumbs/Thumb.vue";
import ThumbAssetService from "@/services/thumb-asset-service";

vi.mock("@/services/thumb-asset-service", () => ({
	default: { acquire: vi.fn(), invalidate: vi.fn() },
}));

vi.mock("@/utils/Helpers", () => ({
	useImageHelpers: () => ({ getNoImageIcon: () => "placeholder.svg" }),
}));

const acquire = vi.mocked(ThumbAssetService.acquire);
const invalidate = vi.mocked(ThumbAssetService.invalidate);

function mountThumb(props: Record<string, unknown>) {
	return mount(Thumb, {
		props: { albumId: "album", photoId: "p1", type: "small2x", ...props },
		global: { mocks: { $t: (key: string) => key } },
	});
}

beforeEach(() => {
	acquire.mockReset();
	acquire.mockReturnValue({ promise: Promise.resolve("blob:asset"), release: vi.fn() });
	invalidate.mockReset();
});

describe("Thumb (S-102-14)", () => {
	it("renders a direct URL without asking /Asset", async () => {
		const wrapper = mountThumb({ directUrl: "/uploads/small2x/p1.jpg" });
		await flushPromises();

		expect(wrapper.find("img").attributes("src")).toBe("/uploads/small2x/p1.jpg");
		expect(acquire).not.toHaveBeenCalled();
	});

	it("shows the placeholder while its URL is pending", async () => {
		const wrapper = mountThumb({ pending: true });
		await flushPromises();

		expect(wrapper.find("img").attributes("src")).toBe("placeholder.svg");
		expect(acquire).not.toHaveBeenCalled();
	});

	it("loads through /Asset when its URL is null", async () => {
		const wrapper = mountThumb({ directUrl: null });
		await flushPromises();

		expect(acquire).toHaveBeenCalledExactlyOnceWith("album", "p1", "small2x");
		expect(wrapper.find("img").attributes("src")).toBe("blob:asset");
	});

	it("loads through /Asset once its pending URL resolves to null", async () => {
		const wrapper = mountThumb({ pending: true });
		await wrapper.setProps({ pending: false, directUrl: null });
		await flushPromises();

		expect(acquire).toHaveBeenCalledOnce();
		expect(wrapper.find("img").attributes("src")).toBe("blob:asset");
	});

	it("falls back to /Asset when the direct URL fails to load", async () => {
		const wrapper = mountThumb({ directUrl: "/uploads/small2x/gone.jpg" });
		await wrapper.find("img").trigger("error");
		await flushPromises();

		expect(acquire).toHaveBeenCalledExactlyOnceWith("album", "p1", "small2x");
		expect(wrapper.find("img").attributes("src")).toBe("blob:asset");
	});

	it.each([
		[1, "small"],
		[2, "small2x"],
	])("asks /Asset for the variant of a %ix screen when no type is given", async (ratio, type) => {
		vi.stubGlobal("devicePixelRatio", ratio);
		try {
			mountThumb({ type: undefined, directUrl: null });
			await flushPromises();

			expect(acquire).toHaveBeenCalledExactlyOnceWith("album", "p1", type);
		} finally {
			vi.unstubAllGlobals();
		}
	});

	it("keeps today's /Asset behaviour without the new props", async () => {
		const wrapper = mountThumb({});
		await flushPromises();

		expect(acquire).toHaveBeenCalledOnce();
		expect(wrapper.find("img").attributes("src")).toBe("blob:asset");
	});
});

describe("Thumb on S3 (Feature 113)", () => {
	it("asks /Asset once more when its S3 URL fails to load (S-113-09)", async () => {
		acquire
			.mockReturnValueOnce({ promise: Promise.resolve("https://bucket.example/old.jpg"), release: vi.fn() })
			.mockReturnValueOnce({ promise: Promise.resolve("https://bucket.example/new.jpg"), release: vi.fn() });
		const wrapper = mountThumb({});
		await flushPromises();

		await wrapper.find("img").trigger("error");
		await flushPromises();

		expect(invalidate).toHaveBeenCalledExactlyOnceWith("album", "p1", "small2x");
		expect(acquire).toHaveBeenCalledTimes(2);
		expect(wrapper.find("img").attributes("src")).toBe("https://bucket.example/new.jpg");
	});

	it("shows the placeholder when the fresh S3 URL fails too (S-113-09)", async () => {
		acquire.mockReturnValue({ promise: Promise.resolve("https://bucket.example/gone.jpg"), release: vi.fn() });
		const wrapper = mountThumb({});
		await flushPromises();

		await wrapper.find("img").trigger("error");
		await flushPromises();
		await wrapper.find("img").trigger("error");
		await flushPromises();

		expect(acquire).toHaveBeenCalledTimes(2);
		expect(wrapper.find("img").attributes("src")).toBe("placeholder.svg");
	});

	it("gives up when /Asset hands back the failed direct URL of a public bucket (S-113-09)", async () => {
		acquire.mockReturnValue({ promise: Promise.resolve("https://bucket.example/gone.jpg"), release: vi.fn() });
		const wrapper = mountThumb({ directUrl: "https://bucket.example/gone.jpg" });
		await flushPromises();

		// Direct URL, its `/Asset` fallback, then the one retry: each fails to load.
		for (let attempt = 0; attempt < 3; attempt++) {
			await wrapper.find("img").trigger("error");
			await flushPromises();
		}

		expect(acquire).toHaveBeenCalledTimes(2);
		expect(wrapper.find("img").attributes("src")).toBe("placeholder.svg");
	});

	it("does not retry an object URL that fails to load (S-113-10)", async () => {
		const wrapper = mountThumb({});
		await flushPromises();

		await wrapper.find("img").trigger("error");
		await flushPromises();

		expect(acquire).toHaveBeenCalledOnce();
		expect(invalidate).not.toHaveBeenCalled();
		expect(wrapper.find("img").attributes("src")).toBe("blob:asset");
	});
});
