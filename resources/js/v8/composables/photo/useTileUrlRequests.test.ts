/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { afterEach, beforeEach, describe, expect, it, vi } from "vite-plus/test";
import { createPinia, setActivePinia } from "pinia";
import { type EffectScope, effectScope, nextTick, reactive, ref } from "vue";

const route = reactive<{ name: string | undefined; params: Record<string, string> }>({ name: "album", params: { albumId: "album1" } });

vi.mock("vue-router", () => ({ useRoute: () => route }));
vi.mock("@/services/photo-children-v3-service", () => ({ default: { getUrls: () => new Promise(() => undefined) } }));

import { useTileUrlRequests } from "@/v8/composables/photo/useTileUrlRequests";
import { useTileUrlsStore } from "@/stores/TileUrlsState";
import { useUserStore } from "@/stores/UserState";

const scopes: EffectScope[] = [];

function run(fn: () => void): void {
	const scope = effectScope();
	scopes.push(scope);
	scope.run(fn);
}

function spiedStore() {
	const store = useTileUrlsStore();
	vi.spyOn(store, "request");
	return store;
}

beforeEach(() => {
	setActivePinia(createPinia());
	route.name = "album";
	route.params = { albumId: "album1" };
});

afterEach(() => {
	scopes.splice(0).forEach((scope) => scope.stop());
});

describe("useTileUrlRequests (S-102-13)", () => {
	it("sets the source from the route and asks for the rendered ids right away", () => {
		const store = spiedStore();
		const ids = ref(["a", "b"]);

		run(() => useTileUrlRequests(() => ids.value));

		expect(store.source).toBe("album1");
		expect(store.request).toHaveBeenCalledExactlyOnceWith(["a", "b"]);
	});

	it("follows the route to another source and asks again", async () => {
		const store = spiedStore();
		run(() => useTileUrlRequests(() => ["a"]));

		route.name = "timeline";
		route.params = {};
		await nextTick();

		expect(store.source).toBe("timeline");
		expect(store.request).toHaveBeenCalledTimes(2);
	});

	it("asks again only when the rendered id set changes", async () => {
		const store = spiedStore();
		const ids = ref(["a", "b"]);
		run(() => useTileUrlRequests(() => ids.value));

		ids.value = ["a", "b"];
		await nextTick();
		expect(store.request).toHaveBeenCalledTimes(1);

		ids.value = ["b", "c"];
		await nextTick();
		expect(store.request).toHaveBeenLastCalledWith(["b", "c"]);
		expect(store.request).toHaveBeenCalledTimes(2);
	});

	it("hands the logged-in user to the store, which starts over when it changes", async () => {
		const store = spiedStore();
		const userStore = useUserStore();
		run(() => useTileUrlRequests(() => ["a"]));
		expect(store.viewer).toBeNull();

		userStore.user = { id: 7 } as App.Http.Resources.Models.UserResource;
		await nextTick();

		expect(store.viewer).toBe(7);
		expect(store.request).toHaveBeenCalledTimes(2);
	});

	it("does not ask while nothing is rendered", () => {
		const store = spiedStore();

		run(() => useTileUrlRequests(() => []));

		expect(store.request).not.toHaveBeenCalled();
	});
});
