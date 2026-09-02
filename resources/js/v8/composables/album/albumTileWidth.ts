import { computed, type Ref } from "vue";
import { storeToRefs } from "pinia";
import { useBreakpoints, useWindowSize, breakpointsTailwind } from "@vueuse/core";
import { useLycheeStateStore } from "@/stores/LycheeState";
import { isTouchDevice } from "@/utils/keybindings-utils";

// Same fixed guess `getWidth()` (resources/js/layouts/getWidth.ts) uses for
// the equivalent photo-layout problem. No scrollbar reserves space on touch
// devices (overlay-style), so the guess is 0 there.
const ASSUMED_SCROLLBAR_WIDTH = 15;

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
 * `AlbumThumbVirtual.vue`'s width classes, extracted here as the single
 * shared source of truth (FR-063-14): `tileWidth = viewportWidth *
 * vwFraction - remOffset * 16px`, exactly reproducing
 * `sm:w-[calc(25vw-1rem)] md:w-[calc(19vw-1rem)] lg:w-[calc(16vw-1rem)]
 * xl:w-[calc(14vw-1rem)] 2xl:w-[calc(12vw-0.75rem)]`. Uses `viewportWidth`
 * (not the scroll container's own width) because the original CSS is
 * `vw`-relative, not container-relative — `itemsPerRow` below is what uses
 * the container's own width, to fit that viewport-sized tile as many times
 * as it can.
 *
 * `3xl`/`4xl` are deliberately absent: confirmed via the actual production
 * build (`public/build/assets/app-v8-*.css`) that this project has no
 * `--breakpoint-3xl`/`--breakpoint-4xl` custom property anywhere, so
 * Tailwind v4 silently drops every `3xl:`/`4xl:`-prefixed utility at build
 * time (including `AlbumThumb.vue`'s own `3xl:w-[calc(12vw-0.75rem)]
 * 4xl:w-52`) — dead classes, pre-existing, unrelated to this feature.
 * `2xl`'s formula is therefore what actually applies at any viewport width
 * `2xl` and above, with no further breakpoint beyond it.
 */
const TILE_WIDTH_FORMULA: Record<string, { vwFraction: number; remOffset: number }> = {
	sm: { vwFraction: 0.25, remOffset: 1 },
	md: { vwFraction: 0.19, remOffset: 1 },
	lg: { vwFraction: 0.16, remOffset: 1 },
	xl: { vwFraction: 0.14, remOffset: 1 },
	"2xl": { vwFraction: 0.12, remOffset: 0.75 },
};

/**
 * `AlbumThumbPanel.vue`'s existing `gap-1 sm:gap-2 md:gap-4` classes — caps
 * at `md` (16px), no further increase at `lg`/`xl`/`2xl`.
 */
const GAP_PX: Record<string, number> = {
	base: 4,
	sm: 8,
	md: 16,
	lg: 16,
	xl: 16,
	"2xl": 16,
};

const REM_PX = 16;

/**
 * Imperative, synchronous equivalent of `useBreakpoints(breakpointsTailwind).active()`
 * (FR-063-11) — same threshold table, but a plain function of a width
 * number rather than a reactive media-query listener, for
 * `dragAndSelect.ts`'s one-shot snapshot at drag-start.
 */
export function resolveBreakpoint(viewportWidth: number): string {
	const entries = Object.entries(breakpointsTailwind) as [string, number][];
	entries.sort((a, b) => b[1] - a[1]);
	for (const [name, min] of entries) {
		if (viewportWidth >= min) {
			return name;
		}
	}
	return "";
}

export type AlbumTileGeometry = {
	tileWidth: number;
	itemsPerRow: number;
	gap: number;
};

/**
 * Pure form of the analytic tile-geometry formula (FR-063-14, DO-063-09,
 * Q-063-10) — extracted so `dragAndSelect.ts`'s imperative, one-shot
 * `getBoundingClientRect()`-based snapshot (FR-063-11) can compute the exact
 * same geometry a mounted grid uses without itself depending on the
 * `useElementSize`/`useBreakpoints` reactive composables below (whose
 * ResizeObserver-driven updates are asynchronous — unsuitable for a value
 * that must be correct synchronously at drag-start).
 *
 * @param breakpoint Active Tailwind breakpoint key (`""` below `sm`), same
 *                    values `useBreakpoints(breakpointsTailwind).active()` returns.
 */
export function computeAlbumTileGeometry(
	viewportWidth: number,
	containerWidth: number,
	breakpoint: string,
	numberAlbumsPerRowMobile: number,
): AlbumTileGeometry {
	const gap = breakpoint === "" ? GAP_PX.base : (GAP_PX[breakpoint] ?? GAP_PX["2xl"]);

	let itemsPerRow: number;
	if (breakpoint === "") {
		// Below `sm`: mobile explicit-column mode — numberAlbumsPerRowMobile
		// directly, no vw formula (matches AlbumThumb.vue's own
		// w-[calc(100%)]/w-[calc(50%-0.25rem)]/w-[calc(33%-0.25rem)] classes).
		itemsPerRow = Math.max(1, numberAlbumsPerRowMobile);
	} else {
		// The vw formula below gives a *nominal* tile width, used only to
		// decide how many columns should fit — the actually-rendered
		// tileWidth (below) is then stretched to exactly fill the row for
		// that column count, rather than leaving up to one tile+gap of
		// unfilled space on the right whenever containerWidth isn't a clean
		// multiple of (nominal tile + gap). This is a deliberate fluid-grid
		// choice for the virtualized layout (the legacy non-virtualized grid
		// uses fixed vw-widths in a flex-wrap and does leave that leftover
		// space, native to how flex-wrap lays out fixed-size items).
		const formula = TILE_WIDTH_FORMULA[breakpoint] ?? TILE_WIDTH_FORMULA["2xl"];
		const nominalTileWidth = Math.max(0, viewportWidth * formula.vwFraction - formula.remOffset * REM_PX);
		if (nominalTileWidth <= 0 || containerWidth <= 0) {
			itemsPerRow = 1;
		} else {
			// +1e-6 guards against floating-point imprecision at an exact-fit
			// boundary (e.g. containerWidth/nominalTileWidth/gap divide out to
			// exactly N tiles): raw division can land a hair under N (e.g.
			// 6.999999999998), which Math.floor would wrongly round down to
			// N-1, silently losing a whole tile+gap of width to rounding
			// error alone.
			itemsPerRow = Math.max(1, Math.floor((containerWidth + gap) / (nominalTileWidth + gap) + 1e-6));
		}
	}

	// Stretch tiles to exactly fill the row for the chosen itemsPerRow —
	// same formula the mobile branch above has always used, just also
	// applied once itemsPerRow itself is settled for the vw-formula
	// breakpoints.
	const tileWidth = itemsPerRow > 0 ? Math.max(0, (containerWidth - gap * (itemsPerRow - 1)) / itemsPerRow) : containerWidth;

	return { tileWidth, itemsPerRow, gap };
}

export type AlbumTileWidthResult = {
	tileWidth: Readonly<Ref<number>>;
	itemsPerRow: Readonly<Ref<number>>;
	gap: Readonly<Ref<number>>;
};

/**
 * Reactive `itemsPerRow`/`tileWidth` derived analytically from viewport
 * width and the current Tailwind breakpoint — no probe tile, no DOM
 * measurement of any tile or container element (FR-063-14, DO-063-09,
 * Q-063-10). Correct synchronously from the very first evaluation, with no
 * post-mount correction (see WRAPPER_PADDING_PX's own comment for why that
 * matters here specifically).
 */
export function useAlbumTileWidth(): AlbumTileWidthResult {
	const lycheeStore = useLycheeStateStore();
	const { number_albums_per_row_mobile } = storeToRefs(lycheeStore);

	const { width: viewportWidth } = useWindowSize();
	const breakpoints = useBreakpoints(breakpointsTailwind);
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
	const containerWidth = computed(() => {
		const paddingPx = activeBreakpoint.value === "" ? WRAPPER_PADDING_PX_MOBILE : WRAPPER_PADDING_PX;
		const scrollbarWidth = isTouchDevice() ? 0 : ASSUMED_SCROLLBAR_WIDTH;
		return Math.max(0, viewportWidth.value - paddingPx * 2 - scrollbarWidth);
	});

	const geometry = computed<AlbumTileGeometry>(() =>
		computeAlbumTileGeometry(viewportWidth.value, containerWidth.value, activeBreakpoint.value, number_albums_per_row_mobile.value),
	);

	const tileWidth = computed(() => geometry.value.tileWidth);
	const itemsPerRow = computed(() => geometry.value.itemsPerRow);
	const gap = computed(() => geometry.value.gap);

	return { tileWidth, itemsPerRow, gap };
}
