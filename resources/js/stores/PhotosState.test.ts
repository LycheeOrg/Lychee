/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { beforeEach, describe, expect, it } from "vite-plus/test";
import { createPinia, setActivePinia } from "pinia";
import { watch } from "vue";
import { usePhotosStore } from "@/stores/PhotosState";

function photos(prefix: string, n: number): App.Http.Resources.Models.PhotoResource[] {
	return Array.from({ length: n }, (_, i) => ({ id: `${prefix}${i}`, timeline: null }) as unknown as App.Http.Resources.Models.PhotoResource);
}

/** Number of reactive triggers on `photoPageMap` while `run` executes. */
function pageMapTriggers(store: ReturnType<typeof usePhotosStore>, run: () => void): number {
	let triggers = 0;
	const stop = watch(
		() => store.photoPageMap,
		() => {
			triggers++;
		},
		{ deep: true, flush: "sync" },
	);
	run();
	stop();
	return triggers;
}

beforeEach(() => {
	setActivePinia(createPinia());
});

describe("photoPageMap (FR-103-03, S-103-11)", () => {
	it("records a batch with one write", () => {
		const store = usePhotosStore();
		expect(pageMapTriggers(store, () => store.setPhotos(photos("p", 50), false, 1))).toBe(1);
		expect(store.photoPageMap).toEqual(Object.fromEntries(photos("p", 50).map((p) => [p.id, 1])));
	});

	it("keeps earlier pages when appending and prepending", () => {
		const store = usePhotosStore();
		store.setPhotos(photos("b", 3), false, 2);
		expect(pageMapTriggers(store, () => store.appendPhotos(photos("c", 3), false, 3))).toBe(1);
		expect(pageMapTriggers(store, () => store.prependPhotos(photos("a", 3), false, 1))).toBe(1);
		expect(store.photoPageMap).toEqual({ a0: 1, a1: 1, a2: 1, b0: 2, b1: 2, b2: 2, c0: 3, c1: 3, c2: 3 });
	});
});
