/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { describe, expect, it, vi } from "vite-plus/test";
import { createDetailRequests } from "@/utils/detailRequests";

function deferred() {
	let resolve!: () => void;
	let reject!: (e: Error) => void;
	const promise = new Promise<void>((res, rej) => {
		resolve = res;
		reject = rej;
	});
	return { promise, resolve, reject };
}

describe("createDetailRequests (FR-103-07, FR-103-08)", () => {
	it("requests only ids neither merged nor in flight", async () => {
		const requests = createDetailRequests();
		const first = deferred();
		const fetch = vi.fn((_ids: string[]) => first.promise);

		const a = requests.load(["a", "b", "c"], (id) => id === "c", fetch);
		const b = requests.load(["b", "d"], () => false, fetch);
		first.resolve();

		expect(fetch.mock.calls.map((c) => c[0])).toEqual([["a", "b"], ["d"]]);
		await expect(a).resolves.toBe(true);
		await expect(b).resolves.toBe(true);
	});

	it("reports a failed request to every caller waiting for its ids", async () => {
		const requests = createDetailRequests();
		const first = deferred();
		vi.spyOn(console, "error").mockImplementation(() => {});

		const a = requests.load(
			["a"],
			() => false,
			() => first.promise,
		);
		const b = requests.load(
			["a"],
			() => false,
			() => Promise.resolve(),
		);
		first.reject(new Error("offline"));

		await expect(a).resolves.toBe(false);
		await expect(b).resolves.toBe(false);
	});

	it("requests an id again after its request settled", async () => {
		const requests = createDetailRequests();
		const fetch = vi.fn(() => Promise.reject(new Error("offline")));
		vi.spyOn(console, "error").mockImplementation(() => {});

		await requests.load(["a"], () => false, fetch);
		await requests.load(["a"], () => false, fetch);

		expect(fetch).toHaveBeenCalledTimes(2);
	});

	it("succeeds at once when nothing is missing", async () => {
		const requests = createDetailRequests();
		const fetch = vi.fn(() => Promise.resolve());

		await expect(requests.load(["a"], () => true, fetch)).resolves.toBe(true);
		expect(fetch).not.toHaveBeenCalled();
	});
});
