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

describe("ThumbAssetService.acquire()", () => {
	it("keeps the axios response cache whatever CLIENT_CACHE_ENABLED says", () => {
		ThumbAssetService.acquire("album", "p5", "small").release();
		expect(get.mock.calls[0][1]).toMatchObject({ cache: { enabled: true } });
	});
});

// `/Asset` answers an S3 variant with JSON `{ url }` instead of bytes (FR-113-01).
function s3Body(url: unknown): Blob {
	return new Blob([JSON.stringify({ url })], { type: "application/json" });
}

describe("ThumbAssetService on S3 (Feature 113)", () => {
	it("resolves a JSON body to its URL, never creating or revoking an object URL (S-113-06)", async () => {
		get.mockImplementation(() => Promise.resolve({ data: s3Body("https://bucket.example/p6.jpg") }));
		const acquired = ThumbAssetService.acquire("album", "p6", "small");

		await expect(acquired.promise).resolves.toBe("https://bucket.example/p6.jpg");
		expect(URL.createObjectURL).not.toHaveBeenCalled();

		acquired.release();
		ThumbAssetService.clear();
		await flushPromises();
		expect(revokeObjectURL).not.toHaveBeenCalled();
	});

	it("rejects a JSON body without a URL and forgets it (S-113-07)", async () => {
		get.mockImplementation(() => Promise.resolve({ data: s3Body(null) }));
		const failed = ThumbAssetService.acquire("album", "p7", "small");
		await expect(failed.promise).rejects.toThrow();
		failed.release();

		const again = ThumbAssetService.acquire("album", "p7", "small");
		await again.promise.catch(() => {});
		again.release();
		expect(get).toHaveBeenCalledTimes(2);
	});

	it("fetches again after invalidate(), while the old URL is still held (API-113-03)", async () => {
		get.mockImplementation(() => Promise.resolve({ data: s3Body("https://bucket.example/old.jpg") }));
		const held = ThumbAssetService.acquire("album", "p8", "small");
		await held.promise;

		ThumbAssetService.invalidate("album", "p8", "small");
		get.mockImplementation(() => Promise.resolve({ data: s3Body("https://bucket.example/new.jpg") }));
		const fresh = ThumbAssetService.acquire("album", "p8", "small");

		await expect(fresh.promise).resolves.toBe("https://bucket.example/new.jpg");
		expect(get).toHaveBeenCalledTimes(2);
		held.release();
		fresh.release();
	});
});
