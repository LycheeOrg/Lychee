import { useJustify } from "./useJustify";

export default { justify };

export function justify(el, { modifiers }, { cleanup }) {
	const waitPollModifier = modifiers[0];
	const waitPollDuration = modifiers[1] || 2500;

	waitPollModifier === "wait" ? setTimeout(() => useJustify(el), waitPollDuration) : useJustify(el);

	waitPollModifier === "poll" && setInterval(() => useJustify(el), waitPollDuration);

	window.addEventListener("resize", () => useJustify(el));
	window.addEventListener("reload:justify", () => useJustify(el));

	cleanup(() => {
		window.removeEventListener("resize", useJustify);
		window.addEventListener("reload:justify", useJustify);
	});
}
