/**
 * Loading indicator chosen by the server (Feature 084).
 *
 * `Meta` renders `<meta name="lychee-loading-indicator" content="<mode>" data-url="<url>">`
 * into the page, so the very first loader (before the init config arrives) already uses it.
 * The tag is read once and cached; a missing tag or an unknown mode means `default`.
 */
export type LoadingIndicatorMode = "default" | "unbranded" | "custom";

export type LoadingIndicator = {
	mode: LoadingIndicatorMode;
	url: string;
};

const DEFAULT_INDICATOR: LoadingIndicator = { mode: "default", url: "" };

let cached: LoadingIndicator | undefined = undefined;

export function parseLoadingIndicator(meta: Element | null): LoadingIndicator {
	const mode = meta?.getAttribute("content");
	const url = meta?.getAttribute("data-url") ?? "";
	if (mode === "unbranded") {
		return { mode, url: "" };
	}
	if (mode === "custom" && url !== "") {
		return { mode, url };
	}
	return DEFAULT_INDICATOR;
}

export function readLoadingIndicator(): LoadingIndicator {
	cached ??= parseLoadingIndicator(document.querySelector('meta[name="lychee-loading-indicator"]'));
	return cached;
}
