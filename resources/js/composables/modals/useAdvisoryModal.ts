/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { ref } from "vue";
import SecurityAdvisoriesService from "@/services/security-advisories-service";

const DISMISSED_KEY = "advisory_dismissed";

// Module-level reactive state provides intentional singleton behaviour:
// - One advisory check per admin login across all component instances.
// - Dismissed state persists for the duration of the browser session
//   (sessionStorage is cleared when the tab is closed).
const is_visible = ref(false);
const advisories = ref<App.Http.Resources.Models.SecurityAdvisoryResource[]>([]);

/**
 * Composable that handles fetching and displaying the security advisories
 * modal for admin users after login.
 *
 * The modal is shown at most once per browser session (controlled via
 * sessionStorage). Non-admin users receive a 403 from the endpoint, which
 * is caught and silently ignored.
 */
export function useAdvisoryModal() {
	function check() {
		if (sessionStorage.getItem(DISMISSED_KEY) !== null) {
			return;
		}

		SecurityAdvisoriesService.getAdvisories()
			.then((response) => {
				if (response.data.length > 0) {
					advisories.value = response.data;
					is_visible.value = true;
				}
			})
			.catch(() => {
				// 401/403 for non-admins or network errors: silently ignore.
			});
	}

	function dismiss() {
		sessionStorage.setItem(DISMISSED_KEY, "1");
		is_visible.value = false;
	}

	return {
		advisories,
		is_visible,
		check,
		dismiss,
	};
}
