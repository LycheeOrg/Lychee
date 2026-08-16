/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { computed, type ComputedRef, type Ref } from "vue";

/**
 * Anchor + shift positioning for the landing page CTA button, mirroring
 * WatermarkPreview.vue's `watermarkStyle` (same clamp/sign conventions,
 * same relative-%/absolute-px shift semantics) so the two features stay
 * mentally consistent for anyone who's configured a watermark before.
 */
export function useLandingCtaPosition(data: Ref<App.Http.Resources.GalleryConfigs.LandingPageResource>): {
	ctaStyle: ComputedRef<string>;
} {
	const shiftXCss = computed(() => {
		const signed = (data.value.cta_shift_x_direction === "left" ? -1 : 1) * data.value.cta_shift_x;
		return data.value.cta_shift_type === "relative" ? `${signed}%` : `${signed}px`;
	});
	const shiftYCss = computed(() => {
		const signed = (data.value.cta_shift_y_direction === "up" ? -1 : 1) * data.value.cta_shift_y;
		return data.value.cta_shift_type === "relative" ? `${signed}%` : `${signed}px`;
	});

	// left/right/top/bottom are additive with the configured shift; right/bottom are inverted
	// since increasing "right"/"bottom" moves the element towards the center, not away from it.
	// Each is wrapped in clamp(0%, ..., 100%) so a large shift can't push the CTA's anchor edge
	// past the viewport.
	const ctaStyle = computed(() => {
		const pos = data.value.cta_position;
		const sx = shiftXCss.value;
		const sy = shiftYCss.value;

		const positionMap: Record<App.Enum.LandingCtaPosition, string> = {
			"top-left": `top: clamp(0%, calc(0% + ${sy}), 100%); left: clamp(0%, calc(0% + ${sx}), 100%);`,
			top: `top: clamp(0%, calc(0% + ${sy}), 100%); left: clamp(0%, calc(50% + ${sx}), 100%); transform: translateX(-50%);`,
			"top-right": `top: clamp(0%, calc(0% + ${sy}), 100%); right: clamp(0%, calc(0% - ${sx}), 100%);`,
			left: `top: clamp(0%, calc(50% + ${sy}), 100%); left: clamp(0%, calc(0% + ${sx}), 100%); transform: translateY(-50%);`,
			center: `top: clamp(0%, calc(50% + ${sy}), 100%); left: clamp(0%, calc(50% + ${sx}), 100%); transform: translate(-50%, -50%);`,
			right: `top: clamp(0%, calc(50% + ${sy}), 100%); right: clamp(0%, calc(0% - ${sx}), 100%); transform: translateY(-50%);`,
			"bottom-left": `bottom: clamp(0%, calc(0% - ${sy}), 100%); left: clamp(0%, calc(0% + ${sx}), 100%);`,
			bottom: `bottom: clamp(0%, calc(0% - ${sy}), 100%); left: clamp(0%, calc(50% + ${sx}), 100%); transform: translateX(-50%);`,
			"bottom-right": `bottom: clamp(0%, calc(0% - ${sy}), 100%); right: clamp(0%, calc(0% - ${sx}), 100%);`,
		};

		return `position: absolute; ${positionMap[pos]}`;
	});

	return { ctaStyle };
}
