/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { computed, type ComputedRef, type Ref } from "vue";

/**
 * 5-value Tailwind class map for the landing page hero text position,
 * landing-scoped (deliberately not shared with AlbumHeaderPanel.vue's
 * equivalent map: albums and the landing page are different bounded
 * contexts, see Feature 054 spec Design Notes).
 */
const POSITION_CLASSES: Record<App.Enum.LandingTextPosition, string> = {
	top_left: "items-start justify-start text-left pt-24 pl-10 md:pl-20",
	top_right: "items-start justify-end text-right pt-24 pr-10 md:pr-20",
	bottom_left: "items-end justify-start text-left pb-24 pl-10 md:pl-20",
	bottom_right: "items-end justify-end text-right pb-24 pr-10 md:pr-20",
	center: "items-center justify-center text-center",
};

export function useLandingTextPosition(data: Ref<App.Http.Resources.GalleryConfigs.LandingPageResource>): {
	positionClasses: ComputedRef<string>;
} {
	const positionClasses = computed(() => POSITION_CLASSES[data.value.hero_text_position] ?? POSITION_CLASSES.center);

	return { positionClasses };
}
