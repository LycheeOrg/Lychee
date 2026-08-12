/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { computed, type ComputedRef, type Ref } from "vue";

/**
 * Single choke point for `prefers-reduced-motion`: whatever preset the
 * server resolved, a reduced-motion browser always gets `none` (NFR-054-04 /
 * WCAG 2.3.3). Every landing layout must read the animation preset through
 * this composable, never the raw prop, so this guarantee cannot be bypassed.
 */
function prefersReducedMotion(): boolean {
	return typeof window !== "undefined" && typeof window.matchMedia === "function" && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
}

export function useLandingAnimation(data: Ref<App.Http.Resources.GalleryConfigs.LandingPageResource>): {
	effectivePreset: ComputedRef<App.Enum.LandingAnimationPreset>;
	isReducedMotion: ComputedRef<boolean>;
	introDelayClass: ComputedRef<string>;
} {
	const isReducedMotion = computed(() => prefersReducedMotion());
	const effectivePreset = computed<App.Enum.LandingAnimationPreset>(() => (isReducedMotion.value ? "none" : data.value.animation_preset));
	const introDelayClass = computed(() => (data.value.intro_screen_enabled ? "" : "no-intro-delay"));

	return { effectivePreset, isReducedMotion, introDelayClass };
}
