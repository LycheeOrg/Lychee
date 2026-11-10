/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

// @vitest-environment jsdom
import { describe, expect, it, vi } from "vite-plus/test";
import type { Router } from "vue-router";
import { albumHref, openInNewTab } from "@/composables/album/openAlbumInNewTab";

function stubRouter(routeName: string, params: Record<string, string> = {}): Router {
	return {
		currentRoute: { value: { name: routeName, params } },
		resolve: vi.fn((to: { name: string; params: { albumId: string } }) => ({ href: `/resolved/${to.name}/${to.params.albumId}` })),
	} as unknown as Router;
}

describe("albumHref (S-097-04)", () => {
	it("resolves the album route on the gallery", () => {
		const router = stubRouter("gallery");
		expect(albumHref(router, "a1")).toBe("/resolved/album/a1");
		expect(router.resolve).toHaveBeenCalledWith({ name: "album", params: { albumId: "a1" } });
	});

	it("resolves the album route on the tag listing", () => {
		expect(albumHref(stubRouter("tags"), "a1")).toBe("/resolved/album/a1");
	});

	it("resolves the flow album route on the flow", () => {
		expect(albumHref(stubRouter("flow"), "a1")).toBe("/resolved/flow-album/a1");
	});

	it("resolves the tag route while browsing a tag photo", () => {
		expect(albumHref(stubRouter("tag", { photoId: "p1" }), "a1")).toBe("/resolved/tag/a1");
	});
});

describe("openInNewTab (S-097-03)", () => {
	it("opens the URL in a new tab with noopener", () => {
		const open = vi.spyOn(window, "open").mockImplementation(() => null);
		openInNewTab("/gallery/a1");
		expect(open).toHaveBeenCalledWith("/gallery/a1", "_blank", "noopener");
		open.mockRestore();
	});
});
