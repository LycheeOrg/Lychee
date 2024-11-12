import { useKeyModifier } from "@vueuse/core";

/**
 * When looking at keybindings, all strokes are usually captured.
 * We want to avoid doing action when one the following is selected:
 * - textarea
 * - input
 * - select
 *
 * Any key bindings hit while those are focused would break the
 * normal behaviour and produce unexpected results.
 *
 * @returns boolean true if we should ignore the current stroke.
 */
export function shouldIgnoreKeystroke(): boolean {
	const skipped = ["TEXTAREA", "INPUT", "SELECT"];
	if (document.activeElement !== null && skipped.includes(document.activeElement.nodeName)) {
		return true;
	}
	return false;
}

function get_platform() {
	// 2022 way of detecting. Note : this userAgentData feature is available only in secure contexts (HTTPS)
	// @ts-expect-error Legacy stuff
	if (typeof navigator.userAgentData !== "undefined" && navigator.userAgentData != null) {
		// @ts-expect-error Legacy stuff
		return navigator.userAgentData.platform;
	}
	// Deprecated but still works for most of the browser
	if (typeof navigator.platform !== "undefined") {
		if (typeof navigator.userAgent !== "undefined" && /android/.test(navigator.userAgent.toLowerCase())) {
			// android device’s navigator.platform is often set as 'linux', so let’s use userAgent for them
			return "android";
		}
		return navigator.platform;
	}
	return "unknown";
}

const platform = get_platform().toLowerCase();
const isOSX = /mac/.test(platform); // Mac desktop
const isIOS = ["iphone", "ipad", "ipod"].indexOf(platform) >= 0; // Mac iOs
export const isApple = isOSX || isIOS; // Apple device (desktop or iOS)

export const ctrlKeyState = useKeyModifier("Control");
export const metaKeyState = useKeyModifier("Meta");
export const shiftKeyState = useKeyModifier("Shift");

export function modKey() {
	if (isApple) {
		return metaKeyState;
	}
	return ctrlKeyState;
}

export function getModKey() {
	if (isApple) {
		return "Meta";
	}
	return "Ctrl";
}

export function disableCtrlA(): void {
	window.addEventListener(
		"keydown",
		function (e) {
			if (shouldIgnoreKeystroke()) {
				return;
			}

			if (e.code == "KeyA" && modKey()) {
				e.preventDefault();
			}
		},
		false,
	);
}

export function isTouchDevice(): boolean {
	return "ontouchstart" in document.documentElement || navigator.maxTouchPoints > 0;
}
