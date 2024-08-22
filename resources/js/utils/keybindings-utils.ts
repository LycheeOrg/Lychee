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

/* istanbul ignore next @preserve */
export function disableScrollingWithArrowsAndCtrlA(): void {
	window.addEventListener(
		"keydown",
		function (e) {
			if (shouldIgnoreKeystroke()) {
				return;
			}

			if (["Space", "ArrowUp", "ArrowDown", "ArrowLeft", "ArrowRight"].indexOf(e.code) > -1) {
				e.preventDefault();
			}
			if (e.code == "KeyA" && e.ctrlKey) {
				e.preventDefault();
			}
		},
		false,
	);
}
