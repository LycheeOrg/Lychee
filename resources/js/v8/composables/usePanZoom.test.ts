/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

// @vitest-environment jsdom
import { afterEach, describe, expect, it, vi } from "vite-plus/test";
import { mount, type VueWrapper } from "@vue/test-utils";
import { defineComponent, h, ref } from "vue";
import { usePanZoom } from "@/v8/composables/usePanZoom";

let wrapper: VueWrapper | undefined = undefined;

/** Mounts `usePanZoom` on a container holding one image, like `#imageview` in `PhotoBox`. */
function mountPanZoom(zoomable: () => boolean): { container: HTMLElement; image: HTMLElement } {
	const Host = defineComponent({
		setup() {
			const container = ref<HTMLElement | null>(null);
			const image = ref<HTMLElement | null>(null);
			usePanZoom({
				container,
				image,
				zoomable,
				sourceWidth: () => 0,
				wheelNavigates: () => false,
				onTap: () => undefined,
				onContainerResize: () => undefined,
			});
			return () => h("div", { ref: container }, [h("img", { ref: image })]);
		},
	});
	wrapper = mount(Host, { attachTo: document.body });
	const container = wrapper.element as HTMLElement;
	return { container, image: container.querySelector("img") as HTMLElement };
}

function touchMove(target: Element, cancelable = true): TouchEvent {
	const event = new TouchEvent("touchmove", { bubbles: true, cancelable });
	target.dispatchEvent(event);
	return event;
}

afterEach(() => {
	wrapper?.unmount();
	wrapper = undefined;
});

describe("usePanZoom: native fling after a swipe (FR-078-15, S-078-26)", () => {
	it("cancels touchmove on a zoomable photo, so no native fling swallows the next tap", () => {
		const { container } = mountPanZoom(() => true);

		expect(touchMove(container).defaultPrevented).toBe(true);
	});

	it("cancels touchmove that starts on the image inside the container", () => {
		const { image } = mountPanZoom(() => true);

		expect(touchMove(image).defaultPrevented).toBe(true);
	});

	it("keeps native page pinch and pan on a non-zoomable photo", () => {
		const { container } = mountPanZoom(() => false);

		expect(touchMove(container).defaultPrevented).toBe(false);
	});

	it("reads zoomability at event time, so a slideshow or a video switch takes effect at once", () => {
		let zoomable = true;
		const { container } = mountPanZoom(() => zoomable);

		expect(touchMove(container).defaultPrevented).toBe(true);
		zoomable = false;
		expect(touchMove(container).defaultPrevented).toBe(false);
	});

	it("leaves a non-cancelable touchmove alone (Chromium warns when one is cancelled during a scroll)", () => {
		const { container } = mountPanZoom(() => true);
		const event = new TouchEvent("touchmove", { bubbles: true, cancelable: false });
		const preventDefault = vi.spyOn(event, "preventDefault");

		container.dispatchEvent(event);

		expect(preventDefault).not.toHaveBeenCalled();
	});
});
