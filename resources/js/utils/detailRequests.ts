/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * In-flight tier-3 (`details`) requests of one photo source, per photo id (FR-103-08). A later
 * call never requests an id already merged or in flight; it waits for the request that carries
 * it instead, so a failure reaches every caller that needed that id (FR-103-07).
 */
export function createDetailRequests() {
	const inFlight = new Map<string, Promise<boolean>>();

	return {
		/**
		 * Fetches, through `fetch`, the ids of `ids` that `isResolved` rejects and no request
		 * carries yet. Resolves true when every unresolved id of `ids` was fetched without error.
		 */
		async load(ids: string[], isResolved: (id: string) => boolean, fetch: (ids: string[]) => Promise<void>): Promise<boolean> {
			const missing = [...new Set(ids)].filter((id) => !isResolved(id));
			const waiting = missing.flatMap((id) => {
				const request = inFlight.get(id);
				return request === undefined ? [] : [request];
			});
			const toFetch = missing.filter((id) => !inFlight.has(id));

			let own: Promise<boolean> = Promise.resolve(true);
			if (toFetch.length > 0) {
				own = fetch(toFetch).then(
					() => true,
					(error: unknown) => {
						console.error(error);
						return false;
					},
				);
				toFetch.forEach((id) => inFlight.set(id, own));
				void own.then(() => toFetch.forEach((id) => inFlight.get(id) === own && inFlight.delete(id)));
			}

			return (await Promise.all([own, ...waiting])).every((succeeded) => succeeded);
		},
	};
}
