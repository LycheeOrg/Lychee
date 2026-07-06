/**
 * Materializes Nuxt UI's `--ui-color-*`/`--ui-<name>` CSS custom properties at
 * runtime.
 *
 * In a real Nuxt app, `@nuxt/ui`'s `runtime/plugins/colors.js` (a
 * `defineNuxtPlugin`) reads `appConfig.ui.colors` and injects a `<style>` tag
 * with the resolved palette via `useHead()`. None of that runs here — this is
 * a plain Vue SPA (`app-v8.ts`), so `defineNuxtPlugin`/`useNuxtApp`/`useHead`
 * don't exist, and the `ui()` Vite plugin's `colors` option (vite.config.ts)
 * only affects each component's compile-time `tv()` theme config, not these
 * runtime CSS variables. Without this, every Nuxt UI color utility
 * (`bg-default`, `bg-primary`, `text-muted`, ...) resolves an undefined
 * `var(--ui-*)` down to `transparent`/`unset`, letting the browser's own
 * `color-scheme: dark` canvas default (a cool blue-gray in Chromium) show
 * through instead of the intended neutral surface.
 *
 * `neutral` additionally gets a light/dark *family* split (slate/zinc) to
 * match v7's `style/preset.ts` (Aura preset: slate surface in light mode,
 * zinc surface in dark mode) — Nuxt UI's own mechanism only varies the
 * *shade* between light/dark, not the color family, so that part is custom.
 */
import colors from "tailwindcss/colors";

const SHADES = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950] as const;

// Matches vite.config.ts's `ui({ ui: { colors: { primary: "sky", neutral: "slate" } } })`
// plus Nuxt UI's own defaults for the remaining slots (secondary/success/info/warning/error).
const NAMED_COLORS = {
	primary: "sky",
	secondary: "blue",
	success: "green",
	info: "blue",
	warning: "yellow",
	error: "red",
} as const;

const NEUTRAL_LIGHT = "slate";
const NEUTRAL_DARK = "zinc";

function getColor(color: string, shade: number): string {
	const palette = (colors as unknown as Record<string, Record<number, string> | string>)[color];
	if (palette && typeof palette === "object" && shade in palette) {
		return palette[shade];
	}
	return "";
}

function generateShades(key: string, colorName: string): string {
	return SHADES.map((shade) => `--ui-color-${key}-${shade}: ${getColor(colorName, shade)};`).join("\n\t");
}

/** Injects the resolved color CSS custom properties into `<head>`. Call once, before mount. */
export function applyV8Theme(): void {
	const namedShades = Object.entries(NAMED_COLORS)
		.map(([key, colorName]) => generateShades(key, colorName))
		.join("\n\t");
	const namedDefaultShade = Object.keys(NAMED_COLORS)
		.map((key) => `--ui-${key}: var(--ui-color-${key}-500);`)
		.join("\n\t");
	const namedDarkShade = Object.keys(NAMED_COLORS)
		.map((key) => `--ui-${key}: var(--ui-color-${key}-400);`)
		.join("\n\t");

	const css = `
@layer theme {
	:root, :host {
		${namedShades}
		${generateShades("neutral", NEUTRAL_LIGHT)}
	}
	:root, :host, .light {
		${namedDefaultShade}
	}
	.dark {
		${generateShades("neutral", NEUTRAL_DARK)}
		${namedDarkShade}
	}
}`;

	const style = document.createElement("style");
	style.setAttribute("data-v8-ui-colors", "");
	style.textContent = css;
	document.head.appendChild(style);
}
