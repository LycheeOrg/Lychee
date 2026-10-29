/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

// @vitest-environment jsdom
import { beforeEach, describe, expect, it, vi } from "vite-plus/test";
import { flushPromises } from "@vue/test-utils";
import axios from "axios";
import ThumbAssetService from "@/services/thumb-asset-service";

vi.mock("axios", () => ({
	default: { get: vi.fn() },
}));

const get = vi.mocked(axios.get);
const revokeObjectURL = vi.fn();
let objectUrls = 0;

beforeEach(() => {
	get.mockReset();
	get.mockImplementation(() => Promise.resolve({ data: new Blob() }));
	revokeObjectURL.mockReset();
	URL.createObjectURL = vi.fn(() => `blob:${++objectUrls}`);
	URL.revokeObjectURL = revokeObjectURL;
});

describe("ThumbAssetService.clear() (S-102-15)", () => {
	it("fetches an asset again after a clear", async () => {
		const first = ThumbAssetService.acquire("album", "p1", "small");
		await first.promise;
		first.release();
		ThumbAssetService.acquire("album", "p1", "small").release();
		expect(get).toHaveBeenCalledTimes(1);

		ThumbAssetService.clear();
		const again = ThumbAssetService.acquire("album", "p1", "small");
		await again.promise;
		again.release();
		expect(get).toHaveBeenCalledTimes(2);
	});

	it("revokes at once the object URLs nothing renders", async () => {
		const acquired = ThumbAssetService.acquire("album", "p2", "small");
		const url = await acquired.promise;
		acquired.release();

		ThumbAssetService.clear();
		await flushPromises();
		expect(revokeObjectURL).toHaveBeenCalledWith(url);
	});

	it("revokes a rendered object URL at its last release", async () => {
		const first = ThumbAssetService.acquire("album", "p3", "small");
		const second = ThumbAssetService.acquire("album", "p3", "small");
		const url = await first.promise;

		ThumbAssetService.clear();
		first.release();
		await flushPromises();
		expect(revokeObjectURL).not.toHaveBeenCalledWith(url);

		second.release();
		await flushPromises();
		expect(revokeObjectURL).toHaveBeenCalledWith(url);
	});

	it("aborts a request still in flight at its last release", () => {
		get.mockImplementation(() => new Promise(() => {}));
		const acquired = ThumbAssetService.acquire("album", "p4", "small");
		const signal = get.mock.calls[0][1]?.signal as AbortSignal;

		ThumbAssetService.clear();
		expect(signal.aborted).toBe(false);

		acquired.release();
		expect(signal.aborted).toBe(true);
	});
});
