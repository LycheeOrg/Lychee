import { useMasonry } from "./useMasonry";

export default { masonry };

export function masonry(el, { modifiers }, { cleanup }) {
	const waitPollModifier = modifiers[0];
	const waitPollDuration = modifiers[1] || 2500;

	waitPollModifier === "wait" ? setTimeout(() => useMasonry(el), waitPollDuration) : useMasonry(el);

	waitPollModifier === "poll" && setInterval(() => useMasonry(el), waitPollDuration);

	window.addEventListener("resize", () => useMasonry(el));
	window.addEventListener("reload:masonry", () => useMasonry(el));

	cleanup(() => {
		window.removeEventListener("resize", useMasonry);
		window.addEventListener("reload:masonry", useMasonry);
	});
}
