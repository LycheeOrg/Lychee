/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { computed, type ComputedRef, type Ref } from "vue";

export type LandingPreviewOrientation = "landscape" | "portrait";

/**
 * On the real public page, which of the landscape/portrait background images
 * shows is driven purely by the visitor's actual viewport orientation (the
 * `portrait:`/`landscape:` Tailwind variants, i.e. `@media (orientation: …)`).
 * The admin settings preview embeds these layouts in a fixed-size box that
 * never itself matches `(orientation: portrait)` on a desktop browser, so
 * `previewOrientation` lets LandingConfig.vue force one or the other for
 * visualization purposes only — real visitors are unaffected since the prop
 * is left undefined outside the preview.
 */
export function useLandingBackgroundOrientation(previewOrientation: Ref<LandingPreviewOrientation | undefined>): {
	landscapeImageClass: ComputedRef<string>;
	portraitImageClass: ComputedRef<string>;
} {
	const landscapeImageClass = computed(() => {
		if (!previewOrientation.value) {
			return "portrait:hidden";
		}
		return previewOrientation.value === "portrait" ? "hidden" : "";
	});
	const portraitImageClass = computed(() => {
		if (!previewOrientation.value) {
			return "landscape:hidden";
		}
		return previewOrientation.value === "landscape" ? "hidden" : "";
	});

	return { landscapeImageClass, portraitImageClass };
}
