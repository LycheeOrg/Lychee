/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

// @vitest-environment jsdom
import { beforeEach, describe, expect, it, vi } from "vite-plus/test";
import { createPinia, setActivePinia } from "pinia";
import { ref } from "vue";

const { resolve } = vi.hoisted(() => ({
	resolve: vi.fn((to: { name: string; params: { albumId: string } }) => ({ href: `/resolved/${to.name}/${to.params.albumId}` })),
}));

vi.mock("vue-router", () => ({
	useRouter: () => ({ currentRoute: { value: { name: "gallery", params: {} } }, resolve }),
}));

// The real composable fetches the global rights through axios on first use.
vi.mock("@/composables/useGlobalRights", () => ({
	useGlobalRights: () => ({ rights: { value: undefined } }),
}));

import { useContextMenu, type AlbumCallbacks, type PhotoCallbacks } from "@/composables/contextMenus/contextMenu";

type ThumbAlbum = App.Http.Resources.Models.ThumbAlbumResource;

function album(id: string, can: boolean): ThumbAlbum {
	return {
		id,
		is_pinned: false,
		thumb: null,
		rights: { can_edit: can, can_merge: can, can_move: can, can_delete: can, can_download: can },
	} as unknown as ThumbAlbum;
}

function menuFor(selected: ThumbAlbum[]) {
	const { Menu } = useContextMenu(
		{
			selectedAlbum: ref(selected.length === 1 ? selected[0] : undefined),
			selectedAlbums: ref(selected),
			selectedAlbumsIds: ref(selected.map((a) => a.id)),
		},
		{} as PhotoCallbacks,
		{} as AlbumCallbacks,
	);
	return Menu.value;
}

beforeEach(() => {
	setActivePinia(createPinia());
	resolve.mockClear();
});

describe("single-album context menu (v7)", () => {
	it("starts with Open in a new tab, then a divider, then the actions (S-097-01)", () => {
		const menu = menuFor([album("a1", true)]);
		expect(menu[0]).toMatchObject({ label: "gallery.menus.open_in_new_tab", icon: "pi pi-external-link", access: true });
		expect(menu[1]).toMatchObject({ is_divider: true });
		expect(menu[2]).toMatchObject({ label: "gallery.menus.rename" });
	});

	it("shows the entry alone, without divider, when no action is accessible (S-097-02)", () => {
		const menu = menuFor([album("a1", false)]);
		expect(menu.map((item) => item.label ?? "divider")).toEqual(["gallery.menus.open_in_new_tab"]);
	});

	it("opens the album route in a new tab (S-097-03)", () => {
		const open = vi.spyOn(window, "open").mockImplementation(() => null);
		menuFor([album("a1", true)])[0].callback?.();
		expect(resolve).toHaveBeenCalledWith({ name: "album", params: { albumId: "a1" } });
		expect(open).toHaveBeenCalledWith("/resolved/album/a1", "_blank", "noopener");
		open.mockRestore();
	});

	it("has no such entry when several albums are selected (S-097-07)", () => {
		const labels = menuFor([album("a1", true), album("a2", true)]).map((item) => item.label);
		expect(labels).not.toContain("gallery.menus.open_in_new_tab");
		expect(labels[0]).toBe("gallery.menus.merge_all");
	});
});
