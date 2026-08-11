/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { onBeforeUnmount, onMounted, ref, type Ref } from "vue";

/**
 * IntersectionObserver-driven section reveal, used by the `parallax_scroll`
 * animation preset (the only preset that reveals per-section on scroll
 * rather than once on mount - see Feature 054 T-054-35).
 */
export function useScrollReveal(active: Ref<boolean>): {
	el: Ref<HTMLElement | null>;
	isVisible: Ref<boolean>;
} {
	const el = ref<HTMLElement | null>(null);
	const isVisible = ref(false);
	let observer: IntersectionObserver | undefined;

	onMounted(() => {
		if (!active.value || typeof IntersectionObserver === "undefined" || el.value === null) {
			isVisible.value = true;
			return;
		}

		observer = new IntersectionObserver(
			(entries) => {
				entries.forEach((entry) => {
					if (entry.isIntersecting) {
						isVisible.value = true;
						observer?.disconnect();
					}
				});
			},
			{ threshold: 0.15 },
		);
		observer.observe(el.value);
	});

	onBeforeUnmount(() => observer?.disconnect());

	return { el, isVisible };
}
