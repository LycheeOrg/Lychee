/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { ref } from "vue";
import SettingsService from "@/services/settings-service";

// Browser-local override of the admin's global `dark_mode_enabled` setting (which
// only controls the class the server bakes into the initial page load, see
// vueapp.blade.php). Deliberately not synced to the backend - this is a per-browser
// preference, not an account setting.
const STORAGE_KEY = "lychee-dark-mode-override";

function readStoredPreference(): boolean | null {
	try {
		const value = localStorage.getItem(STORAGE_KEY);
		return value === null ? null : value === "dark";
	} catch {
		return null;
	}
}

const isDark = ref(document.body.classList.contains("dark"));

function apply(dark: boolean) {
	document.body.classList.toggle("dark", dark);
	isDark.value = dark;
}

/**
 * Applied once at bootstrap (before mount) so a stored browser-local preference wins
 * over the server-rendered default. No-op when the user has never overridden it.
 */
export function applyStoredDarkModePreference(): void {
	const stored = readStoredPreference();
	if (stored !== null) {
		apply(stored);
	}
}

export function useDarkMode() {
	function toggle() {
		const next = !isDark.value;
		apply(next);
		try {
			localStorage.setItem(STORAGE_KEY, next ? "dark" : "light");
		} catch {
			// localStorage unavailable (private browsing, etc.) - the toggle still applies for this page load.
		}
	}

	/**
	 * Admin variant: persists the choice as the instance-wide `dark_mode_enabled` config
	 * (the same one General.vue's settings toggle writes) instead of a browser-local
	 * override, and clears any previous local override so this browser stops shadowing
	 * the default it just set.
	 */
	function toggleGlobal(): Promise<void> {
		const next = !isDark.value;
		apply(next);
		try {
			localStorage.removeItem(STORAGE_KEY);
		} catch {
			// localStorage unavailable (private browsing, etc.) - the class change still applies.
		}
		return SettingsService.setConfigs({ configs: [{ key: "dark_mode_enabled", value: next ? "1" : "0" }] }).then(() => undefined);
	}

	return { isDark, toggle, toggleGlobal };
}
