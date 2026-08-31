import { computed, type Ref } from "vue";
import { storeToRefs } from "pinia";
import { useBreakpoints, useElementSize, useWindowSize, breakpointsTailwind } from "@vueuse/core";
import { useLycheeStateStore } from "@/stores/LycheeState";

/**
 * `AlbumThumbVirtual.vue`'s width classes, extracted here as the single
 * shared source of truth (FR-062-14): `tileWidth = viewportWidth *
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
 * (FR-062-11) — same threshold table, but a plain function of a width
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
 * Pure form of the analytic tile-geometry formula (FR-062-14, DO-062-09,
 * Q-062-10) — extracted so `dragAndSelect.ts`'s imperative, one-shot
 * `getBoundingClientRect()`-based snapshot (FR-062-11) can compute the exact
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

	let tileWidth: number;
	if (breakpoint === "") {
		// Below `sm`: mobile explicit-column mode — numberAlbumsPerRowMobile
		// directly, no vw formula (matches AlbumThumb.vue's own
		// w-[calc(100%)]/w-[calc(50%-0.25rem)]/w-[calc(33%-0.25rem)] classes).
		const perRow = Math.max(1, numberAlbumsPerRowMobile);
		tileWidth = perRow > 0 ? Math.max(0, (containerWidth - gap * (perRow - 1)) / perRow) : containerWidth;
	} else {
		const formula = TILE_WIDTH_FORMULA[breakpoint] ?? TILE_WIDTH_FORMULA["2xl"];
		tileWidth = Math.max(0, viewportWidth * formula.vwFraction - formula.remOffset * REM_PX);
	}

	let itemsPerRow: number;
	if (breakpoint === "") {
		itemsPerRow = Math.max(1, numberAlbumsPerRowMobile);
	} else if (tileWidth <= 0 || containerWidth <= 0) {
		itemsPerRow = 1;
	} else {
		itemsPerRow = Math.max(1, Math.floor((containerWidth + gap) / (tileWidth + gap)));
	}

	return { tileWidth, itemsPerRow, gap };
}

export type AlbumTileWidthResult = {
	tileWidth: Readonly<Ref<number>>;
	itemsPerRow: Readonly<Ref<number>>;
	gap: Readonly<Ref<number>>;
};

/**
 * Reactive `itemsPerRow`/`tileWidth` derived analytically from viewport
 * width, the scroll container's own width, and the current Tailwind
 * breakpoint — no probe tile, no DOM measurement of any tile element
 * (FR-062-14, DO-062-09, Q-062-10).
 *
 * @param containerRef The scroll container element (`AlbumThumbGridVirtual.vue`'s
 *                      own root) — its own width (not the viewport's) is what
 *                      `itemsPerRow` divides by.
 */
export function useAlbumTileWidth(containerRef: Ref<HTMLElement | undefined>): AlbumTileWidthResult {
	const lycheeStore = useLycheeStateStore();
	const { number_albums_per_row_mobile } = storeToRefs(lycheeStore);

	const { width: viewportWidth } = useWindowSize();
	const { width: containerWidth } = useElementSize(containerRef);
	const breakpoints = useBreakpoints(breakpointsTailwind);
	const activeBreakpoint = breakpoints.active();

	const geometry = computed<AlbumTileGeometry>(() =>
		computeAlbumTileGeometry(viewportWidth.value, containerWidth.value, activeBreakpoint.value, number_albums_per_row_mobile.value),
	);

	const tileWidth = computed(() => geometry.value.tileWidth);
	const itemsPerRow = computed(() => geometry.value.itemsPerRow);
	const gap = computed(() => geometry.value.gap);

	return { tileWidth, itemsPerRow, gap };
}
