/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import AuthService from "@/services/auth-service";
import Constants from "@/services/constants";
import { useClearViewerState } from "@/composables/useClearViewerState";

/**
 * Logs the current user out, wipes every identity-dependent store/cache and
 * reloads on /home. Shared by the left menu and the Spotlight palette.
 */
export function useLogout() {
	const { clearViewerState } = useClearViewerState();

	function logout(): Promise<void> {
		return AuthService.logout().then(() => {
			clearViewerState();
			window.location.href = Constants.BASE_URL + "/home";
		});
	}

	return { logout };
}
