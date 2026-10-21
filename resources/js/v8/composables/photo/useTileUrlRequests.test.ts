/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it, vi } from "vite-plus/test";
import { effectScope, nextTick, ref } from "vue";
import { createTileUrlLoader, type TileUrlLoader } from "@/v8/composables/photo/tileUrlLoader";
import { useTileUrlRequests } from "@/v8/composables/photo/useTileUrlRequests";

function spiedLoader(): TileUrlLoader {
	const loader = createTileUrlLoader(() => new Promise(() => undefined));
	vi.spyOn(loader, "request");
	return loader;
}

function run(fn: () => void): void {
	effectScope().run(fn);
}

describe("useTileUrlRequests (S-102-13)", () => {
	it("asks for the rendered ids right away", () => {
		const loader = spiedLoader();
		const ids = ref(["a", "b"]);

		run(() => useTileUrlRequests(loader, () => ids.value));

		expect(loader.request).toHaveBeenCalledExactlyOnceWith(["a", "b"]);
	});

	it("asks again only when the rendered id set changes", async () => {
		const loader = spiedLoader();
		const ids = ref(["a", "b"]);
		run(() => useTileUrlRequests(loader, () => ids.value));

		ids.value = ["a", "b"];
		await nextTick();
		expect(loader.request).toHaveBeenCalledTimes(1);

		ids.value = ["b", "c"];
		await nextTick();
		expect(loader.request).toHaveBeenLastCalledWith(["b", "c"]);
		expect(loader.request).toHaveBeenCalledTimes(2);
	});

	it("asks again for the same ids after the loader is reset", async () => {
		const loader = spiedLoader();
		const ids = ref(["a"]);
		run(() => useTileUrlRequests(loader, () => ids.value));

		loader.reset();
		await nextTick();

		expect(loader.request).toHaveBeenCalledTimes(2);
		expect(loader.request).toHaveBeenLastCalledWith(["a"]);
	});

	it("does not ask while nothing is rendered", () => {
		const loader = spiedLoader();

		run(() => useTileUrlRequests(loader, () => []));

		expect(loader.request).not.toHaveBeenCalled();
	});

	it("does nothing without a loader (search, other grids)", () => {
		const renderedIds = vi.fn(() => ["a"]);

		run(() => useTileUrlRequests(undefined, renderedIds));

		expect(renderedIds).not.toHaveBeenCalled();
	});
});
