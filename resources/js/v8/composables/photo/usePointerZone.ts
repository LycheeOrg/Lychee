/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */
import { useEventListener } from "@vueuse/core";
import { ref, type Ref } from "vue";

/**
 * Tracks whether the pointer is inside the bounding box of an element without
 * the element having to receive pointer events itself.
 *
 * The photo view overlays (dock, rating, next/previous) use large invisible
 * reveal zones. With CSS :hover those zones must catch pointer events, which
 * makes them swallow clicks meant for the layers underneath (e.g. the face
 * overlay). Pairing this with `pointer-events-none` on the zone keeps the
 * reveal effect while letting clicks fall through.
 */
export function usePointerZone(zone: Ref<HTMLElement | null>) {
	const isInside = ref(false);

	function update(event: PointerEvent) {
		const rect = zone.value?.getBoundingClientRect();
		isInside.value =
			rect !== undefined &&
			event.clientX >= rect.left &&
			event.clientX <= rect.right &&
			event.clientY >= rect.top &&
			event.clientY <= rect.bottom;
	}

	useEventListener(window, "pointermove", update, { passive: true });
	useEventListener(window, "pointerdown", update, { passive: true });
	useEventListener(document.documentElement, "pointerleave", () => (isInside.value = false), { passive: true });

	return { isInside };
}
