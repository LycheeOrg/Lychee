import { trans } from "laravel-vue-i18n";

export function useTranslation() {
	function t(key: string, defaultTranslation: string): string {
		const translation = trans(key);
		if (translation === key) {
			// value is not translated
			return defaultTranslation;
		}
		return translation;
	}

	function tDoc(config: { key: string; documentation: string }): string {
		return t("all_settings.documentation." + config.key, config.documentation);
	}

	function tDetails(config: { key: string; details: string }): string {
		return t("all_settings.details." + config.key, config.details);
	}

	function tCatName(config: { key: string; name: string }): string {
		return t("all_settings.category_name." + config.key, config.name);
	}

	function tCatDesc(config: { key: string; description: string }): string {
		return t("all_settings.category_description." + config.key, config.description);
	}

	return {
		t,
		tDoc,
		tDetails,
		tCatName,
		tCatDesc,
	};
}
