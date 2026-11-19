/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { defineStore } from "pinia";
import InitService from "@/services/init-service";

export type GlobalRightsStore = ReturnType<typeof useGlobalRightsStore>;

/**
 * Global rights of the current visitor (Auth::rights), fetched only once something reads them.
 * They depend on identity: call `refresh()` on login/logout.
 */
export const useGlobalRightsStore = defineStore("global-rights-store", {
	state: () => ({
		rights: undefined as App.Http.Resources.Rights.GlobalRightsResource | undefined,
		_loadPromise: undefined as Promise<void> | undefined,
		// Bumped by `reset()` so a request started before it can't commit the previous identity's rights.
		_loadGeneration: 0 as number,
	}),
	actions: {
		/**
		 * Fetches the rights unless already loaded; concurrent callers share the same in-flight request.
		 * A failed request leaves `rights` undefined, so the next call tries again.
		 */
		ensureLoaded(): Promise<void> {
			if (this.rights !== undefined) {
				return Promise.resolve();
			}
			if (this._loadPromise !== undefined) {
				return this._loadPromise;
			}

			const generation = this._loadGeneration;
			const promise = InitService.fetchGlobalRights()
				.then((response) => {
					if (this._loadGeneration === generation) {
						this.rights = response.data;
					}
				})
				.finally(() => {
					if (this._loadGeneration === generation) {
						this._loadPromise = undefined;
					}
				});

			this._loadPromise = promise;
			return promise;
		},

		/** Drops the rights, and fetches them again if they had already been requested. */
		refresh(): Promise<void> {
			const wasRequested = this.rights !== undefined || this._loadPromise !== undefined;
			this.reset();
			return wasRequested ? this.ensureLoaded() : Promise.resolve();
		},

		reset() {
			this.rights = undefined;
			this._loadPromise = undefined;
			this._loadGeneration++;
		},
	},
});
