/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { beforeEach, describe, expect, it, vi } from "vite-plus/test";
import { createPinia, setActivePinia } from "pinia";

type PhotoUrlResource = App.Http.Resources.V3.PhotoUrlResource;
type Pending = { source: string; ids: string[]; density: number; resolve: (r: PhotoUrlResource) => void; reject: (e: Error) => void };

const pending: Pending[] = [];
const getUrls = vi.fn(
	(source: string, ids: string[], density: number) =>
		new Promise<{ data: PhotoUrlResource }>((resolve, reject) => {
			pending.push({ source, ids, density, resolve: (data) => resolve({ data }), reject });
		}),
);

vi.mock("@/services/photo-children-v3-service", () => ({ default: { getUrls: (...args: [string, string[], number]) => getUrls(...args) } }));

import { tileUrlSource, TILE_URL_BATCH_SIZE } from "@/v8/composables/photo/tileUrlLoader";
import { useTileUrlsStore } from "@/stores/TileUrlsState";

/** Direct URLs for every requested id. */
function direct(ids: string[]): PhotoUrlResource {
	return { ids, grid_urls: ids.map((id) => `/uploads/small2x/${id}.jpg`), thumb_urls: ids.map((id) => `/uploads/thumb/${id}.jpg`) };
}

/** Protected viewer: every entry null. */
function protectedUrls(ids: string[]): PhotoUrlResource {
	return { ids, grid_urls: ids.map(() => null), thumb_urls: ids.map(() => null) };
}

const ids = (n: number, prefix = "p") => Array.from({ length: n }, (_, i) => `${prefix}${i}`);
const URLS_OF_A = { grid: "/uploads/small2x/a.jpg", list: "/uploads/thumb/a.jpg" };
const NO_URLS = { grid: null, list: null };

function storeOn(source: string | undefined) {
	const store = useTileUrlsStore();
	store.setSource(source);
	return store;
}

beforeEach(() => {
	setActivePinia(createPinia());
	pending.length = 0;
	getUrls.mockClear();
});

describe("tileUrlSource (S-102-13)", () => {
	it("names the album of the album routes", () => {
		expect(tileUrlSource({ name: "album", params: { albumId: "abc" } })).toBe("abc");
		expect(tileUrlSource({ name: "flow-album", params: { albumId: "abc", photoId: "p" } })).toBe("abc");
	});

	it("names the timeline", () => {
		expect(tileUrlSource({ name: "timeline", params: { date: "2024-01" } })).toBe("timeline");
	});

	it("names nothing elsewhere", () => {
		expect(tileUrlSource({ name: "search", params: { albumId: "abc" } })).toBeUndefined();
		expect(tileUrlSource({ name: "tag", params: { tagId: "1" } })).toBeUndefined();
		expect(tileUrlSource({ name: "album", params: {} })).toBeUndefined();
		expect(tileUrlSource({ name: undefined, params: {} })).toBeUndefined();
	});
});

describe("TileUrlsState: batching (S-102-13)", () => {
	it("asks the source for each id once, in-flight and resolved ids included", async () => {
		const store = storeOn("album1");

		store.request(["a", "b"]);
		store.request(["a", "b", "c"]);
		expect(getUrls.mock.calls.map((c) => [c[0], c[1]])).toEqual([
			["album1", ["a", "b"]],
			["album1", ["c"]],
		]);

		pending[0].resolve(direct(["a", "b"]));
		pending[1].resolve(direct(["c"]));
		await vi.waitFor(() => expect(store.urlsOf("c")).toBeDefined());

		store.request(["a", "b", "c"]);
		expect(getUrls).toHaveBeenCalledTimes(2);
	});

	it("splits a request into chunks of at most 300 ids", () => {
		const store = storeOn("album1");

		store.request(ids(650));

		expect(TILE_URL_BATCH_SIZE).toBe(300);
		expect(getUrls.mock.calls.map((c) => c[1].length)).toEqual([300, 300, 50]);
	});

	it("sends nothing for an empty or fully known id list", () => {
		const store = storeOn("album1");

		store.request([]);
		store.request(["a"]);
		store.request(["a"]);

		expect(getUrls).toHaveBeenCalledTimes(1);
	});

	it("sends nothing and gives null without a source (search, other routes)", () => {
		const store = storeOn(undefined);

		store.request(["a"]);

		expect(getUrls).not.toHaveBeenCalled();
		expect(store.urlsOf("a")).toBeNull();
	});
});

describe("TileUrlsState: entries (S-102-13, S-102-14)", () => {
	it("is pending until the response, then holds both URLs", async () => {
		const store = storeOn("album1");

		store.request(["a"]);
		expect(store.urlsOf("a")).toBeUndefined();

		pending[0].resolve(direct(["a"]));
		await vi.waitFor(() => expect(store.urlsOf("a")).toEqual(URLS_OF_A));
	});

	it("gives null URLs to ids the response left out, without disabling", async () => {
		const store = storeOn("album1");

		store.request(["a", "gone"]);
		pending[0].resolve(direct(["a"]));

		await vi.waitFor(() => expect(store.urlsOf("gone")).toEqual(NO_URLS));
		expect(store.disabled).toBe(false);
	});

	it("gives null URLs to every id of a failed request, without disabling", async () => {
		const store = storeOn("album1");

		store.request(["a", "b"]);
		pending[0].reject(new Error("offline"));

		await vi.waitFor(() => expect(store.urlsOf("b")).toEqual(NO_URLS));
		expect(store.urlsOf("a")).toEqual(NO_URLS);
		expect(store.disabled).toBe(false);
	});
});

describe("TileUrlsState: across sources (S-102-13)", () => {
	it("keeps resolved URLs when the source changes", async () => {
		const store = storeOn("album1");
		store.request(["a"]);
		pending[0].resolve(direct(["a"]));
		await vi.waitFor(() => expect(store.urlsOf("a")).toEqual(URLS_OF_A));

		store.setSource("timeline");
		store.request(["a", "b"]);

		expect(store.urlsOf("a")).toEqual(URLS_OF_A);
		expect(getUrls).toHaveBeenLastCalledWith("timeline", ["b"], expect.any(Number));
	});

	it("asks the new source again for ids the previous one left out", async () => {
		const store = storeOn("album1");
		store.request(["x"]);
		pending[0].resolve(direct([]));
		await vi.waitFor(() => expect(store.urlsOf("x")).toEqual(NO_URLS));

		store.setSource("album2");

		expect(store.urlsOf("x")).toBeUndefined();
		store.request(["x"]);
		expect(getUrls).toHaveBeenLastCalledWith("album2", ["x"], expect.any(Number));
	});

	it("bumps its generation on a source change only", () => {
		const store = storeOn("album1");
		const generation = store.generation;

		store.setSource("album1");
		expect(store.generation).toBe(generation);

		store.setSource("album2");
		expect(store.generation).toBe(generation + 1);
	});

	it("stores the URLs of a late response from the previous source, but not its misses", async () => {
		const store = storeOn("album1");
		store.request(["a", "gone"]);
		store.setSource("album2");

		pending[0].resolve(direct(["a"]));
		await vi.waitFor(() => expect(store.urlsOf("a")).toEqual(URLS_OF_A));

		expect(store.urlsOf("gone")).toBeUndefined();
	});
});

describe("TileUrlsState: protected viewer (S-102-13)", () => {
	it("stops asking after a non-empty all-null response", async () => {
		const store = storeOn("album1");

		store.request(["a"]);
		pending[0].resolve(protectedUrls(["a"]));
		await vi.waitFor(() => expect(store.disabled).toBe(true));

		store.request(["b"]);
		expect(getUrls).toHaveBeenCalledTimes(1);
		expect(store.urlsOf("b")).toBeNull();
		expect(store.urlsOf("a")).toBeNull();
	});

	it("keeps asking after an empty response or a mixed one", async () => {
		const store = storeOn("album1");

		store.request(["gone"]);
		pending[0].resolve(direct([]));
		await vi.waitFor(() => expect(store.urlsOf("gone")).toEqual(NO_URLS));

		store.request(["a", "b"]);
		pending[1].resolve({ ids: ["a", "b"], grid_urls: [null, "/uploads/small2x/b.jpg"], thumb_urls: [null, null] });
		await vi.waitFor(() => expect(store.urlsOf("b")).toBeDefined());

		expect(store.disabled).toBe(false);
		store.request(["c"]);
		expect(getUrls).toHaveBeenCalledTimes(3);
	});

	it("asks again once the source changes", async () => {
		const store = storeOn("album1");
		store.request(["a"]);
		pending[0].resolve(protectedUrls(["a"]));
		await vi.waitFor(() => expect(store.disabled).toBe(true));

		store.setSource("album2");
		store.request(["b"]);

		expect(store.disabled).toBe(false);
		expect(getUrls).toHaveBeenCalledTimes(2);
	});
});

describe("TileUrlsState: viewer change (S-102-13)", () => {
	it("keeps everything while the viewer stays the same", async () => {
		const store = storeOn("album1");
		store.setViewer(1);
		store.request(["a"]);
		pending[0].resolve(direct(["a"]));
		await vi.waitFor(() => expect(store.urlsOf("a")).toEqual(URLS_OF_A));

		store.setViewer(1);

		expect(store.urlsOf("a")).toEqual(URLS_OF_A);
	});

	it("empties the store, re-enables it and bumps its generation", async () => {
		const store = storeOn("album1");
		store.setViewer(null);
		store.request(["a"]);
		pending[0].resolve(protectedUrls(["a"]));
		await vi.waitFor(() => expect(store.disabled).toBe(true));
		const generation = store.generation;

		store.setViewer(7);

		expect(store.disabled).toBe(false);
		expect(store.generation).toBe(generation + 1);
		expect(store.urlsOf("a")).toBeUndefined();
		store.request(["a"]);
		expect(getUrls).toHaveBeenCalledTimes(2);
	});

	it("ignores responses that arrive after the viewer changed", async () => {
		const store = storeOn("album1");
		store.setViewer(null);
		store.request(["a"]);
		store.setViewer(7);

		pending[0].resolve(direct(["a"]));
		await Promise.resolve();
		await Promise.resolve();

		expect(store.urlsOf("a")).toBeUndefined();
	});
});
