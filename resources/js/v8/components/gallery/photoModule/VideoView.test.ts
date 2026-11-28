/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

// @vitest-environment jsdom
import { afterEach, describe, expect, it, vi } from "vite-plus/test";
import { mount, type VueWrapper } from "@vue/test-utils";
import VideoView from "@/v8/components/gallery/photoModule/VideoView.vue";

// The Video.js custom elements are not needed to check how events leave the player.
vi.mock("@videojs/html/video/player", () => ({}));
vi.mock("@videojs/html/video/skin", () => ({}));

let wrapper: VueWrapper | undefined;
let host: HTMLElement | undefined;

/** Mounts the player inside a host that records what bubbles out of it, as `#imageview` does. */
function mountInHost(type: string) {
	host = document.createElement("div");
	document.body.append(host);
	const listener = vi.fn();
	host.addEventListener(type, listener);
	wrapper = mount(VideoView, {
		props: { src: "/uploads/original/v.mp4", width: 1920, height: 1080, autoplay: false, loop: false },
		attachTo: host,
	});
	return listener;
}

afterEach(() => {
	wrapper?.unmount();
	host?.remove();
});

describe("VideoView", () => {
	it("keeps a click in the player out of the lightbox, which would cycle the overlay", () => {
		const listener = mountInHost("click");

		wrapper!.find("video").element.dispatchEvent(new MouseEvent("click", { bubbles: true }));

		expect(listener).not.toHaveBeenCalled();
	});

	it("lets a click on the letterbox around the player reach the lightbox", () => {
		const listener = mountInHost("click");

		wrapper!.element.dispatchEvent(new MouseEvent("click", { bubbles: true }));

		expect(listener).toHaveBeenCalledOnce();
	});

	it("lets a pointerdown in the player bubble: Video.js closes its menus from the document", () => {
		const listener = mountInHost("pointerdown");

		wrapper!.find("video").element.dispatchEvent(new Event("pointerdown", { bubbles: true }));

		expect(listener).toHaveBeenCalledOnce();
	});
});
