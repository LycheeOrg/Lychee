import { addCollection } from "@iconify/vue";
import { icons as lucideIcons } from "@iconify-json/lucide";

/**
 * Registers the Lucide Iconify collection offline so icon lookups never hit the
 * public Iconify API at runtime - see Feature 049 T-049-03. Lychee must be usable
 * with zero network connection, so any new icon set must be added here as a
 * local @iconify-json/* dependency rather than resolved remotely.
 */
export function registerIconCollections(): void {
	addCollection(lucideIcons);
}

/**
 * Lucide ships a single (outline) style with no dedicated "filled" glyphs, unlike
 * the old PrimeIcons "-fill" suffix pairs (e.g. heart / heart-fill) v8 used to
 * render. Append this class alongside an outline icon's `name` to force its
 * SVG path's `fill="none"` to `currentColor`, approximating a solid/filled look
 * (e.g. a "favourited" heart, a "highlighted" flag).
 */
export const FILL_OVERRIDE_CLASS = "[&>path]:fill-current";
