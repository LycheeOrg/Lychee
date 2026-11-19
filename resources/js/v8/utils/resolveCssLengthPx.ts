/**
 * Resolves a CSS length (e.g. `var(--ui-header-height)`, `3.5rem`) to a px
 * number the way the browser itself would — assigns it as the height of a
 * throwaway element and reads back its computed (always-px) height. Needed
 * because `getComputedStyle(el).getPropertyValue("--some-var")` returns the
 * raw, un-resolved value (e.g. `"3.5rem"`), not a px number.
 */
export function resolveCssLengthPx(cssValue: string): number {
	const probe = document.createElement("div");
	probe.style.position = "absolute";
	probe.style.visibility = "hidden";
	probe.style.height = cssValue;
	document.body.appendChild(probe);
	const px = parseFloat(getComputedStyle(probe).height) || 0;
	probe.remove();
	return px;
}
