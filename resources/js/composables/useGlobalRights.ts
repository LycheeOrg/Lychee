/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { storeToRefs } from "pinia";
import { useGlobalRightsStore } from "@/stores/GlobalRightsState";

/**
 * Global rights for a component that needs them: triggers the (shared, at most once) fetch.
 * Components mounted on every page (LeftMenu, and SpotlightSearch in v8) read the store directly
 * and call `ensureLoaded()` only once they open, so pages that never need the rights never fetch them.
 */
export function useGlobalRights() {
	const store = useGlobalRightsStore();
	// Failures are already reported by the axios interceptor; the next caller retries.
	store.ensureLoaded().catch(() => {});
	const { rights } = storeToRefs(store);

	return { rights };
}
