/**
 * Numeric width/height ratio for each `AspectRatioCSSType` —
 * `AlbumThumbVirtual.vue` still uses the CSS class itself for its own
 * `aspect-*` class binding; this is only for the row-height math in
 * `virtualAlbumRows.ts`, which needs a plain number.
 */
const ASPECT_RATIO_NUMBER: Record<App.Enum.AspectRatioCSSType, number> = {
	"aspect-5x4": 5 / 4,
	"aspect-4x5": 4 / 5,
	"aspect-3x2": 3 / 2,
	"aspect-2x3": 2 / 3,
	"aspect-square": 1,
	"aspect-video": 16 / 9,
};

export function aspectRatioCssToNumber(cssType: App.Enum.AspectRatioCSSType | undefined): number {
	return cssType !== undefined ? (ASPECT_RATIO_NUMBER[cssType] ?? 1) : 1;
}
