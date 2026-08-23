/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { computed, type ComputedRef, type Ref } from "vue";
import { loadLanguageAsync } from "laravel-vue-i18n";
import SettingsService from "@/services/settings-service";
import { availableLocales } from "@/v8/i18n";

/**
 * Available-languages list plus a switcher that persists the choice as the shared
 * `lang` config. Gated on `canEdit` since only admins may change it. The list itself
 * comes from `@/v8/i18n`'s build-time locale bundle (the same one app-v8.ts's i18n
 * loader uses) rather than a network call.
 */
export function useLanguageSwitcher(canEdit: Ref<boolean>): {
	availableLanguages: ComputedRef<string[]>;
	setLanguage: (code: string) => void;
} {
	const availableLanguages = computed(() => (canEdit.value ? availableLocales : []));

	function setLanguage(code: string) {
		SettingsService.setConfigs({ configs: [{ key: "lang", value: code }] }).then(() => {
			loadLanguageAsync(code).then(() => {
				document.documentElement.lang = code;
				document.documentElement.dir = ["ar", "fa"].includes(code) ? "rtl" : "ltr";
			});
		});
	}

	return { availableLanguages, setLanguage };
}
