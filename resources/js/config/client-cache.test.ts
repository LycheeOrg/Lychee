/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

// @vitest-environment jsdom
import { describe, expect, it } from "vite-plus/test";
import axios, { type AxiosAdapter, type InternalAxiosRequestConfig } from "axios";
import type { AxiosCacheInstance } from "axios-cache-interceptor";
import { parseClientCacheEnabled, setUpClientCache } from "@/config/client-cache";

function metaTag(content: string): Element {
	const meta = document.createElement("meta");
	meta.setAttribute("name", "lychee-client-cache");
	meta.setAttribute("content", content);
	return meta;
}

// An instance whose requests never leave the test: every one is recorded and answered with a
// response the cache would keep for an hour.
function stubbedInstance(enabled: boolean): { instance: AxiosCacheInstance; sent: InternalAxiosRequestConfig[] } {
	const sent: InternalAxiosRequestConfig[] = [];
	const instance = axios.create();
	instance.defaults.adapter = ((config: InternalAxiosRequestConfig) => {
		sent.push(config);
		return Promise.resolve({ data: "asset", status: 200, statusText: "OK", headers: { "cache-control": "private, max-age=3600" }, config });
	}) as AxiosAdapter;
	setUpClientCache(instance, enabled);
	return { instance: instance as unknown as AxiosCacheInstance, sent };
}

describe("parseClientCacheEnabled()", () => {
	it("is off when the tag says 0", () => {
		expect(parseClientCacheEnabled(metaTag("0"))).toBe(false);
	});

	it("is on when the tag says 1", () => {
		expect(parseClientCacheEnabled(metaTag("1"))).toBe(true);
	});

	it("is on without the tag", () => {
		expect(parseClientCacheEnabled(null)).toBe(true);
	});
});

describe("setUpClientCache()", () => {
	it("answers a repeated request from its storage when on", async () => {
		const { instance, sent } = stubbedInstance(true);

		await instance.get("Asset/album/photo/small", { id: "asset" });
		const second = await instance.get("Asset/album/photo/small", { id: "asset" });

		expect(sent).toHaveLength(1);
		expect(second.cached).toBe(true);
	});

	it("sends every request to the network with the no-cache headers when off", async () => {
		const { instance, sent } = stubbedInstance(false);

		await instance.get("Asset/album/photo/small", { id: "asset" });
		await instance.get("Asset/album/photo/small", { id: "asset" });

		expect(sent).toHaveLength(2);
		for (const config of sent) {
			expect(config.headers.get("Cache-Control")).toBe("no-cache, no-store, must-revalidate, max-age=0");
			expect(config.headers.get("Pragma")).toBe("no-cache");
			expect(config.headers.get("Expires")).toBe("0");
		}
	});

	it("still answers a request that opts in from its storage when off", async () => {
		const { instance, sent } = stubbedInstance(false);

		await instance.get("Asset/album/photo/small", { id: "asset", cache: { enabled: true } });
		const second = await instance.get("Asset/album/photo/small", { id: "asset", cache: { enabled: true } });

		expect(sent).toHaveLength(1);
		expect(second.cached).toBe(true);
	});

	it("keeps its storage when off, so cache clearing still works", () => {
		const { instance } = stubbedInstance(false);

		expect(() => instance.storage.remove("asset")).not.toThrow();
	});
});
