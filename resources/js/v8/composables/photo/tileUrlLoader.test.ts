/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it, vi } from "vite-plus/test";
import { createTileUrlLoader, TILE_URL_BATCH_SIZE, type TileUrlFetcher } from "@/v8/composables/photo/tileUrlLoader";

type PhotoUrlResource = App.Http.Resources.V3.PhotoUrlResource;

/** Direct URLs for every requested id. */
function direct(ids: string[]): PhotoUrlResource {
	return { ids, small2x_urls: ids.map((id) => `/uploads/small2x/${id}.jpg`), thumb_urls: ids.map((id) => `/uploads/thumb/${id}.jpg`) };
}

/** Protected viewer: every entry null. */
function protectedUrls(ids: string[]): PhotoUrlResource {
	return { ids, small2x_urls: ids.map(() => null), thumb_urls: ids.map(() => null) };
}

/** A fetcher whose calls stay pending until `settle()`. */
function deferredFetcher() {
	const pending: { ids: string[]; resolve: (r: PhotoUrlResource) => void; reject: (e: Error) => void }[] = [];
	const fetcher = vi.fn<TileUrlFetcher>(
		(ids) =>
			new Promise<PhotoUrlResource>((resolve, reject) => {
				pending.push({ ids, resolve, reject });
			}),
	);
	return { fetcher, pending };
}

const ids = (n: number, prefix = "p") => Array.from({ length: n }, (_, i) => `${prefix}${i}`);

describe("tileUrlLoader: batching (S-102-13)", () => {
	it("asks for each id once, in-flight and resolved ids included", async () => {
		const { fetcher, pending } = deferredFetcher();
		const loader = createTileUrlLoader(fetcher);

		loader.request(["a", "b"]);
		loader.request(["a", "b", "c"]);
		expect(fetcher.mock.calls.map((c) => c[0])).toEqual([["a", "b"], ["c"]]);

		pending[0].resolve(direct(["a", "b"]));
		pending[1].resolve(direct(["c"]));
		await vi.waitFor(() => expect(loader.urlsOf("c")).toBeDefined());

		loader.request(["a", "b", "c"]);
		expect(fetcher).toHaveBeenCalledTimes(2);
	});

	it("splits a request into chunks of at most 300 ids", () => {
		const { fetcher } = deferredFetcher();
		const loader = createTileUrlLoader(fetcher);

		loader.request(ids(650));

		expect(TILE_URL_BATCH_SIZE).toBe(300);
		expect(fetcher.mock.calls.map((c) => c[0].length)).toEqual([300, 300, 50]);
	});

	it("sends nothing for an empty or fully known id list", () => {
		const { fetcher } = deferredFetcher();
		const loader = createTileUrlLoader(fetcher);

		loader.request([]);
		loader.request(["a"]);
		loader.request(["a"]);

		expect(fetcher).toHaveBeenCalledTimes(1);
	});
});

describe("tileUrlLoader: entries (S-102-13, S-102-14)", () => {
	it("is pending until the response, then holds both URLs", async () => {
		const { fetcher, pending } = deferredFetcher();
		const loader = createTileUrlLoader(fetcher);

		loader.request(["a"]);
		expect(loader.urlsOf("a")).toBeUndefined();

		pending[0].resolve(direct(["a"]));
		await vi.waitFor(() => expect(loader.urlsOf("a")).toEqual({ grid: "/uploads/small2x/a.jpg", list: "/uploads/thumb/a.jpg" }));
	});

	it("gives null URLs to ids the response left out", async () => {
		const { fetcher, pending } = deferredFetcher();
		const loader = createTileUrlLoader(fetcher);

		loader.request(["a", "gone"]);
		pending[0].resolve(direct(["a"]));

		await vi.waitFor(() => expect(loader.urlsOf("gone")).toEqual({ grid: null, list: null }));
		expect(loader.disabled.value).toBe(false);
	});

	it("gives null URLs to every id of a failed request, without disabling", async () => {
		const { fetcher, pending } = deferredFetcher();
		const loader = createTileUrlLoader(fetcher);

		loader.request(["a", "b"]);
		pending[0].reject(new Error("offline"));

		await vi.waitFor(() => expect(loader.urlsOf("b")).toEqual({ grid: null, list: null }));
		expect(loader.urlsOf("a")).toEqual({ grid: null, list: null });
		expect(loader.disabled.value).toBe(false);
	});
});

describe("tileUrlLoader: protected viewer (S-102-13)", () => {
	it("stops asking after a non-empty all-null response", async () => {
		const { fetcher, pending } = deferredFetcher();
		const loader = createTileUrlLoader(fetcher);

		loader.request(["a"]);
		pending[0].resolve(protectedUrls(["a"]));
		await vi.waitFor(() => expect(loader.disabled.value).toBe(true));

		loader.request(["b"]);
		expect(fetcher).toHaveBeenCalledTimes(1);
		expect(loader.urlsOf("b")).toBeNull();
		expect(loader.urlsOf("a")).toBeNull();
	});

	it("keeps asking after an empty response or a mixed one", async () => {
		const { fetcher, pending } = deferredFetcher();
		const loader = createTileUrlLoader(fetcher);

		loader.request(["gone"]);
		pending[0].resolve(direct([]));
		await vi.waitFor(() => expect(loader.urlsOf("gone")).toEqual({ grid: null, list: null }));

		loader.request(["a", "b"]);
		pending[1].resolve({ ids: ["a", "b"], small2x_urls: [null, "/uploads/small2x/b.jpg"], thumb_urls: [null, null] });
		await vi.waitFor(() => expect(loader.urlsOf("b")).toBeDefined());

		expect(loader.disabled.value).toBe(false);
		loader.request(["c"]);
		expect(fetcher).toHaveBeenCalledTimes(3);
	});
});

describe("tileUrlLoader: reload (S-102-13)", () => {
	it("ignores responses that arrive after a reset, and asks again", async () => {
		const { fetcher, pending } = deferredFetcher();
		const loader = createTileUrlLoader(fetcher);

		loader.request(["a"]);
		loader.reset();
		pending[0].resolve(direct(["a"]));
		await Promise.resolve();
		await Promise.resolve();

		expect(loader.urlsOf("a")).toBeUndefined();
		loader.request(["a"]);
		expect(fetcher).toHaveBeenCalledTimes(2);
	});

	it("starts over after a reset, re-enabled", async () => {
		const { fetcher, pending } = deferredFetcher();
		const loader = createTileUrlLoader(fetcher);

		loader.request(["a"]);
		pending[0].resolve(protectedUrls(["a"]));
		await vi.waitFor(() => expect(loader.disabled.value).toBe(true));

		loader.reset();

		expect(loader.disabled.value).toBe(false);
		expect(loader.urlsOf("a")).toBeUndefined();
		loader.request(["a"]);
		expect(fetcher).toHaveBeenCalledTimes(2);
	});
});
