/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

import { computed } from "vue";
import { useDarkMode } from "@/v8/composables/useDarkMode";

/**
 * Colours of the Insights charts, read from the Nuxt UI tokens
 * (Feature 085, NFR-085-07).
 *
 * Tokens are `oklch(...)` values, which ECharts cannot interpolate; each one is
 * painted on a 1×1 canvas and read back as `rgb(...)`. Recomputed when the
 * colour mode changes.
 */
export type InsightsPalette = {
	text: string;
	muted: string;
	border: string;
	surface: string;
	/** Card background, used for the gaps between calendar squares. */
	background: string;
	primary: string;
	/** Semantic colours: firsts, lasts and milestones of the timeline. */
	success: string;
	error: string;
	warning: string;
	/** Four steps from faint to full primary, for heatmap thresholds. */
	steps: [string, string, string, string];
};

let canvas: HTMLCanvasElement | undefined;

function toRgb(color: string, alpha = 1): string {
	canvas ??= document.createElement("canvas");
	canvas.width = 1;
	canvas.height = 1;
	const context = canvas.getContext("2d", { willReadFrequently: true });
	if (context === null || color === "") {
		return color;
	}
	context.clearRect(0, 0, 1, 1);
	context.fillStyle = color;
	context.fillRect(0, 0, 1, 1);
	const [r, g, b] = context.getImageData(0, 0, 1, 1).data;
	return alpha === 1 ? `rgb(${r}, ${g}, ${b})` : `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function token(name: string): string {
	return getComputedStyle(document.body).getPropertyValue(name).trim();
}

export function useInsightsTheme() {
	const { isDark } = useDarkMode();

	const palette = computed<InsightsPalette>(() => {
		// Read for reactivity: the tokens change with the `dark` class.
		void isDark.value;
		const primary = token("--ui-primary");

		return {
			text: toRgb(token("--ui-text")),
			muted: toRgb(token("--ui-text-muted")),
			border: toRgb(token("--ui-border")),
			surface: toRgb(token("--ui-bg-elevated")),
			background: toRgb(token("--ui-bg")),
			primary: toRgb(primary),
			success: toRgb(token("--ui-success")),
			error: toRgb(token("--ui-error")),
			warning: toRgb(token("--ui-warning")),
			steps: [toRgb(primary, 0.25), toRgb(primary, 0.5), toRgb(primary, 0.75), toRgb(primary)],
		};
	});

	return { palette };
}
