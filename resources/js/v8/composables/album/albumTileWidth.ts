import { computed, type Ref } from "vue";
import { storeToRefs } from "pinia";
import { useBreakpoints, useWindowSize, breakpointsTailwind } from "@vueuse/core";
import { isTouchDevice } from "@/utils/keybindings-utils";
import { useTogglablesStateStore } from "@/stores/ModalsState";
import { useAlbumDateScrubberState } from "@/v8/composables/album/albumDateScrubberState";
import { resolveCssLengthPx } from "@/v8/utils/resolveCssLengthPx";
import { useEffectiveAlbumTileSettings } from "@/v8/composables/gallerySizes/gallerySizesLive";

/**
 * `breakpointsTailwind` (`@vueuse/core`) only carries Tailwind's own
 * `sm`/`md`/`lg`/`xl`/`2xl` defaults — this project's `@theme` block
 * (`resources/sass/app-v8.css`) additionally defines `3xl`/`4xl`/`5xl`/`6xl`.
 * The tile width no longer depends on the breakpoint (Feature 110), but the
 * gap and the mobile/desktop split still do, and `resolveBreakpoint()` keeps
 * returning the same names the grids and `dragAndSelect.ts` already use.
 * Scoped to this file only (not a global override of vueuse's export).
 */
const ALBUM_TILE_BREAKPOINTS = {
	...breakpointsTailwind,
	"3xl": 1792,
	"4xl": 2048,
	"5xl": 2304,
	"6xl": 2560,
};

/**
 * Same fixed guess `getWidth()` (resources/js/layouts/getWidth.ts) uses for
 * the equivalent photo-layout problem. No scrollbar reserves space on touch
 * devices (overlay-style), so callers pass 0 there.
 */
export const ASSUMED_SCROLLBAR_WIDTH = 15;

// The grid/list root's own wrapper is `class="w-full px-4 sm:px-6"`
// (AlbumThumbGridVirtual.vue / AlbumListViewVirtual.vue) — computed
// analytically from the breakpoint rather than read off the DOM via
// getComputedStyle(containerRef.value?.parentElement): that element is
// `undefined` until mount, so a DOM read starts at padding=0 and only
// becomes correct afterwards, a spurious one-time containerWidth change
// that (empirically) can desync tanstack-virtual's cached item positions
// from its own freshly-recomputed total size for a large album. Since
// viewportWidth/breakpoint are both available synchronously pre-mount,
// this keeps containerWidth correct from the very first evaluation, with
// no such transition to correct from.
const WRAPPER_PADDING_PX = 24; // px-6 (sm and above)
const WRAPPER_PADDING_PX_MOBILE = 16; // px-4 (below sm)

/**
 * Default of the `album_thumb_min_width` setting (Feature 110, DO-110-01),
 * also used when the store holds a non-positive value. 175 px reproduces the
 * column count of the former per-breakpoint vw table at 59 % of window widths
 * from 640 to 2559 px, and stays within one column at 87 % (spec Appendix A).
 */
export const DEFAULT_ALBUM_THUMB_MIN_WIDTH = 175;

/** Default of the `album_thumb_gap` setting (Feature 110, FR-110-16): gives the former 4/8/16 px. */
export const DEFAULT_ALBUM_THUMB_GAP = 16;

/**
 * Gap between album tiles for a breakpoint (FR-110-16, Q-110-05 A): the
 * `album_thumb_gap` value from `md` (768 px), half of it at `sm`, a quarter
 * below `sm`, rounded. Negative or non-finite values use the default.
 */
export function albumGapForBreakpoint(breakpoint: string, gapSetting: number): number {
	const gap = Number.isFinite(gapSetting) && gapSetting >= 0 ? gapSetting : DEFAULT_ALBUM_THUMB_GAP;
	if (breakpoint === "") {
		return Math.round(gap / 4);
	}
	return breakpoint === "sm" ? Math.round(gap / 2) : gap;
}

/** `TimelineDatesV3.vue`'s rail (`w-16`), mounted beside the grids of `AlbumPanel.vue`. */
const DATE_SCRUBBER_WIDTH_PX = 64;

/** `--nav-bar-width`, resolved once on first use: it is a constant of the stylesheet. */
let navBarWidthPx: number | undefined = undefined;
function getNavBarWidthPx(): number {
	navBarWidthPx ??= resolveCssLengthPx("var(--nav-bar-width)");
	return navBarWidthPx;
}

/**
 * Imperative, synchronous equivalent of `useBreakpoints(ALBUM_TILE_BREAKPOINTS).active()`
 * — same threshold table, but a plain function of a width
 * number rather than a reactive media-query listener, for one-shot
 * snapshots (`dragAndSelect.ts`) and simulated widths (Feature 110 preview).
 */
export function resolveBreakpoint(viewportWidth: number): string {
	const entries = Object.entries(ALBUM_TILE_BREAKPOINTS) as [string, number][];
	entries.sort((a, b) => b[1] - a[1]);
	for (const [name, min] of entries) {
		if (viewportWidth >= min) {
			return name;
		}
	}
	return "";
}

/**
 * Width the album grid gets inside a window of `viewportWidth`: the wrapper's
 * padding (by breakpoint), the scrollbar (`ASSUMED_SCROLLBAR_WIDTH`, or 0 on
 * touch devices) and whatever sits beside the grid (inline nav panel, date
 * scrubber) are subtracted. Pure, so the Feature 110 preview can lay out for
 * a simulated window exactly as the gallery does for the real one.
 */
export function computeAlbumContainerWidth(viewportWidth: number, breakpoint: string, scrollbarWidth: number, sideChromeWidth: number): number {
	const paddingPx = breakpoint === "" ? WRAPPER_PADDING_PX_MOBILE : WRAPPER_PADDING_PX;
	return Math.max(0, viewportWidth - paddingPx * 2 - scrollbarWidth - sideChromeWidth);
}

export type AlbumTileGeometry = {
	tileWidth: number;
	itemsPerRow: number;
	gap: number;
};

/** The three album size settings (stored, or the live drawer's while it is open). */
export type AlbumTileSettings = {
	/** `album_thumb_min_width` in CSS px; non-positive values fall back to the default. */
	minWidth: number;
	/** `number_albums_per_row_mobile`, used below `sm`. */
	perRowMobile: number;
	/** `album_thumb_gap` in CSS px (full value from `md`, see `albumGapForBreakpoint`). */
	gap: number;
};

/**
 * Pure form of the analytic tile-geometry formula — shared by the mounted
 * grids (through `useAlbumTileWidth()`), `dragAndSelect.ts`'s one-shot
 * snapshot and the Feature 110 preview.
 *
 * At `sm` and above (FR-110-01, ADR-110-01): as many columns of at least
 * `minWidth` as fit the container, then the columns share the leftover width,
 * so `minWidth ≤ tileWidth < minWidth + (minWidth + gap) / itemsPerRow`.
 * Only the container width decides: a row holding fewer albums than columns
 * keeps the same tile width and leaves the remaining slots empty. Below `sm`
 * (FR-110-03), `perRowMobile` decides the column count. The gap follows
 * `albumGapForBreakpoint()` (FR-110-16).
 *
 * @param breakpoint Active breakpoint key (`""` below `sm`), same values
 *                    `useBreakpoints(ALBUM_TILE_BREAKPOINTS).active()` returns.
 */
export function computeAlbumTileGeometry(containerWidth: number, breakpoint: string, settings: AlbumTileSettings): AlbumTileGeometry {
	const gap = albumGapForBreakpoint(breakpoint, settings.gap);
	const min = settings.minWidth > 0 ? settings.minWidth : DEFAULT_ALBUM_THUMB_MIN_WIDTH;

	let itemsPerRow: number;
	if (breakpoint === "") {
		itemsPerRow = Math.max(1, settings.perRowMobile);
	} else if (containerWidth <= 0) {
		itemsPerRow = 1;
	} else {
		// +1e-6 guards against floating-point imprecision at an exact-fit
		// boundary: raw division can land a hair under N (e.g.
		// 6.999999999998), which Math.floor would wrongly round down to N-1.
		itemsPerRow = Math.max(1, Math.floor((containerWidth + gap) / (min + gap) + 1e-6));
	}

	const tileWidth = Math.max(0, (containerWidth - gap * (itemsPerRow - 1)) / itemsPerRow);

	return { tileWidth, itemsPerRow, gap };
}

export type AlbumTileWidthResult = {
	tileWidth: Readonly<Ref<number>>;
	itemsPerRow: Readonly<Ref<number>>;
	gap: Readonly<Ref<number>>;
};

/**
 * Reactive `itemsPerRow`/`tileWidth` derived analytically from viewport
 * width, the current breakpoint and `album_thumb_min_width` (or the live
 * drawer's value) — no probe tile,
 * no DOM measurement of any tile or container element. Correct synchronously
 * from the very first evaluation, with no post-mount correction (see
 * WRAPPER_PADDING_PX's own comment for why that matters here specifically).
 */
export function useAlbumTileWidth(withDateScrubber: boolean = false): AlbumTileWidthResult {
	// Stored values, or the live drawer's while it is open (Feature 110, FR-110-14).
	const { minWidth, perRowMobile, gap: gapSetting } = useEffectiveAlbumTileSettings();
	const { isNavOpen } = storeToRefs(useTogglablesStateStore());
	const { isAvailable: isDateScrubberAvailable, isHidden: isDateScrubberHidden } = useAlbumDateScrubberState();
	const isNavPanelInline = useBreakpoints(breakpointsTailwind).greaterOrEqual("lg");

	const { width: viewportWidth } = useWindowSize();
	const breakpoints = useBreakpoints(ALBUM_TILE_BREAKPOINTS);
	const activeBreakpoint = breakpoints.active();

	// containerWidth is derived analytically (viewportWidth minus the
	// wrapper's own known padding minus an assumed scrollbar width), not
	// measured live off the container via ResizeObserver/getBoundingClientRect
	// — same reasoning as getWidth() (resources/js/layouts/getWidth.ts) and
	// PhotoThumbPanelList.vue, which solve this exact problem the same way.
	// Reading the container's own rect is tempting but wrong here: this
	// grid's own itemsPerRow decision determines its total height, which
	// determines whether the page needs a vertical scrollbar, and a
	// scrollbar appearing/disappearing changes the container's own rendered
	// width — so measuring the container directly closes a feedback loop
	// (width -> itemsPerRow -> height -> scrollbar -> width -> ...) that can
	// jitter. viewportWidth is the full viewport and is unaffected by a
	// scrollbar appearing, so it's stable input into that loop; we correct
	// for the scrollbar it doesn't account for with the same fixed guess
	// getWidth() uses, rather than measuring it live.
	//
	// (Two earlier attempts tracked containerWidth off the DOM instead —
	// once via ResizeObserver, which turned out to be unreliable because
	// this container's inline height can run into the hundreds of thousands
	// of px for a large virtualized album, expensive enough to lay out that
	// it can stall RO's callback queue entirely (confirmed directly, a raw
	// ResizeObserver on such an element received zero callbacks); once via
	// reading the wrapper's getComputedStyle padding, which is `undefined`
	// until mount and so still produced one spurious containerWidth change
	// right after mount — which, empirically, was enough to desync
	// tanstack-virtual's cached item positions from its own freshly
	// recomputed total size on a large album. Deriving padding from the
	// breakpoint (a synchronously-available value, no DOM/mount dependency)
	// avoids that transition entirely.)
	// Width taken beside the grid by AlbumNavPanel.vue (inline on desktop only, a slideover
	// below lg) and, for AlbumPanel.vue's grids, the date scrubber rail. Not subtracted, the
	// tiles overflow under them and drag selection hit-boxes no longer match the rendered tiles.
	// The scrubber counts even while a photo is open (and the rail hidden), so opening a photo
	// does not relayout the grid underneath.
	const sideChromeWidth = computed(() => {
		const nav = isNavOpen.value && isNavPanelInline.value ? getNavBarWidthPx() : 0;
		const scrubber = withDateScrubber && isDateScrubberAvailable.value && !isDateScrubberHidden.value ? DATE_SCRUBBER_WIDTH_PX : 0;
		return nav + scrubber;
	});

	const containerWidth = computed(() =>
		computeAlbumContainerWidth(viewportWidth.value, activeBreakpoint.value, isTouchDevice() ? 0 : ASSUMED_SCROLLBAR_WIDTH, sideChromeWidth.value),
	);

	const geometry = computed<AlbumTileGeometry>(() =>
		computeAlbumTileGeometry(containerWidth.value, activeBreakpoint.value, {
			minWidth: minWidth.value,
			perRowMobile: perRowMobile.value,
			gap: gapSetting.value,
		}),
	);

	const tileWidth = computed(() => geometry.value.tileWidth);
	const itemsPerRow = computed(() => geometry.value.itemsPerRow);
	const gap = computed(() => geometry.value.gap);

	return { tileWidth, itemsPerRow, gap };
}
