import { computed, type ComputedRef } from "vue";
import { trans } from "laravel-vue-i18n";
import type { SpotlightItem } from "./types";

export function useSpotlightLanguageItems(
	availableLanguages: ComputedRef<string[]>,
	setLanguage: (code: string) => void,
	close: () => void,
): ComputedRef<SpotlightItem[]> {
	return computed(() =>
		availableLanguages.value.map((code) => ({
			label: code,
			description: trans("search-palette.language"),
			icon: "lucide:languages",
			kind: "nav",
			onSelect: () => {
				close();
				setLanguage(code);
			},
		})),
	);
}
