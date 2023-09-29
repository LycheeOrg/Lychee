import { useGrid } from "./useGrid";

export default { grid };

export function grid(el, { modifiers }, { cleanup }) {
	const waitPollModifier = modifiers[0];
	const waitPollDuration = modifiers[1] || 2500;

	waitPollModifier === "wait" ? setTimeout(() => useGrid(el), waitPollDuration) : useGrid(el);

	waitPollModifier === "poll" && setInterval(() => useGrid(el), waitPollDuration);

	window.addEventListener("resize", () => useGrid(el));
	window.addEventListener("reload:grid", () => useGrid(el));

	cleanup(() => {
		window.removeEventListener("resize", useGrid);
		window.addEventListener("reload:grid", useGrid);
	});
}
