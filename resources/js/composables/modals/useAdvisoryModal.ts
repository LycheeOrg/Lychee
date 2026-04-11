/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { ref } from "vue";
import SecurityAdvisoriesService from "@/services/security-advisories-service";
import { useLeftMenuStateStore } from "@/stores/LeftMenuState";

const DISMISSED_KEY = "advisory_dismissed";

// Module-level reactive state provides intentional singleton behaviour:
// - One advisory check per admin login across all component instances.
// - Dismissed state persists for the duration of the browser session
//   (sessionStorage is cleared when the tab is closed).
const isAdvisoriesVisible = ref(false);
const advisories = ref<App.Http.Resources.Models.SecurityAdvisoryResource[]>([]);

/**
 * Composable that handles fetching and displaying the security advisories
 * modal for admin users after login.
 *
 * The modal is shown at most once per browser session (controlled via
 * sessionStorage). The advisory endpoint is only queried when the current
 * user has admin-level rights, as determined by the left-menu global rights
 * data already loaded in LeftMenuStateStore.
 */
export function useAdvisoryModal() {
	const leftMenuStore = useLeftMenuStateStore();

	function advisoryCheck() {
		const initData = leftMenuStore.initData;
		const isAdmin =
			initData?.settings.can_edit ||
			initData?.user_management.can_edit ||
			initData?.settings.can_see_diagnostics ||
			initData?.settings.can_see_logs ||
			initData?.settings.can_acess_user_groups ||
			false;

		if (!isAdmin) {
			return;
		}

		if (sessionStorage.getItem(DISMISSED_KEY) !== null) {
			return;
		}

		SecurityAdvisoriesService.getAdvisories()
			.then((response) => {
				if (response.data.length > 0) {
					advisories.value = response.data;
					isAdvisoriesVisible.value = true;
				}
			})
			.catch(() => {
				// Network errors: silently ignore.
			});
	}

	function advisoryDismiss() {
		sessionStorage.setItem(DISMISSED_KEY, "1");
		isAdvisoriesVisible.value = false;
	}

	return {
		advisories,
		isAdvisoriesVisible,
		advisoryCheck,
		advisoryDismiss,
	};
}
