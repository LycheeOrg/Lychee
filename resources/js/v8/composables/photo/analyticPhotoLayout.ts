import { justified, masonry, grid, square } from "@/v8/layouts/wasmLayouts";
import { HEADER_ROW_HEIGHT, LIST_ROW_HEIGHT } from "@/v8/composables/album/virtualAlbumRows";
import { filterBucketedTiles, type AlbumBucketBoundary, type TimelineBucketBoundary } from "@/v8/utils/albumBucketBoundaries";

export type PhotoLayoutMode = "justified" | "square" | "masonry" | "grid" | "list";

export type PhotoBox = { top: number; left: number; width: number; height: number };

/**
 * Every one of the four WASM layout primitives shares this exact
 * `(ratios: Float64Array, containerWidth, target, gap) => {containerHeight, boxes}`
 * signature (confirmed directly against `useJustify.ts`/`useSquare.ts`/
 * `useMasonry.ts`/`useGrid.ts`) — no DOM dependency in any of the four,
 * the DOM-reading/writing is entirely each `useX.ts` wrapper's own choice.
 * `list` needs no WASM call at all (Decision Card Q-065-01/Q-065-04).
 */
export function computeBucketLayout(
	mode: PhotoLayoutMode,
	ratios: number[],
	containerWidth: number,
	target: number,
	gap: number,
): { boxes: PhotoBox[]; containerHeight: number } {
	if (ratios.length === 0) {
		return { boxes: [], containerHeight: 0 };
	}

	if (mode === "list") {
		const boxes = ratios.map((_, i) => ({ top: i * (LIST_ROW_HEIGHT + gap), left: 0, width: containerWidth, height: LIST_ROW_HEIGHT }));
		return { boxes, containerHeight: ratios.length * (LIST_ROW_HEIGHT + gap) - gap };
	}

	const fn = { justified, square, masonry, grid }[mode];
	const result = fn(Float64Array.from(ratios), containerWidth, target, gap);
	return { boxes: result.boxes, containerHeight: result.containerHeight };
}

export type PhotoHeaderTop = { top: number; label: string; bucketId: string };

export type PhotoLayoutResult = {
	/** Index-aligned with the flat tile/ratios array, mounted or not. */
	boxes: PhotoBox[];
	totalHeight: number;
	headerTops: PhotoHeaderTop[];
};

/**
 * Computes every tile's exact box up front, for a whole album, by calling
 * `computeBucketLayout()` once per bucket run and concatenating the results
 * top-to-bottom (FR-065-07/08) — a header pseudo-box (`HEADER_ROW_HEIGHT`
 * tall) precedes each bucket's own boxes when `showHeaders` is true. Never
 * reads or writes a live DOM node.
 */
export function computePhotoLayout(
	mode: PhotoLayoutMode,
	ratios: number[],
	buckets: AlbumBucketBoundary[],
	showHeaders: boolean,
	containerWidth: number,
	target: number,
	gap: number,
): PhotoLayoutResult {
	const boxes: PhotoBox[] = new Array(ratios.length);
	const headerTops: PhotoHeaderTop[] = [];
	let runningTop = 0;

	for (const bucket of buckets) {
		if (showHeaders) {
			headerTops.push({ top: runningTop, label: bucket.label, bucketId: bucket.bucketId });
			runningTop += HEADER_ROW_HEIGHT + gap;
		}

		const bucketRatios = ratios.slice(bucket.startIndex, bucket.startIndex + bucket.count);
		const { boxes: bucketBoxes, containerHeight } = computeBucketLayout(mode, bucketRatios, containerWidth, target, gap);
		for (let i = 0; i < bucketBoxes.length; i++) {
			const b = bucketBoxes[i];
			boxes[bucket.startIndex + i] = { top: b.top + runningTop, left: b.left, width: b.width, height: b.height };
		}
		runningTop += containerHeight + gap;
	}

	const totalHeight = buckets.length > 0 ? Math.max(0, runningTop - gap) : 0;
	return { boxes, totalHeight, headerTops };
}

export type PhotoChunk = {
	key: string;
	/** Absolute top, in the same coordinate space as `PhotoLayoutResult.boxes`. */
	top: number;
	/** Virtualizer row size — the stride to the next chunk's top (or this chunk's own content height if it's the last one). A pure virtualization bookkeeping value, not a layout concept. */
	size: number;
	tileStart: number;
	tileCount: number;
	header: { label: string; bucketId: string } | null;
};

/**
 * Splits a bucket's tiles into consecutive runs that share the same `top` —
 * one real visual row for row-uniform layouts (justified/square/grid/list),
 * one column-fill "step" for masonry's column-packed geometry — read
 * directly off the already-computed boxes. A justified/masonry row's tile
 * count depends on each tile's own aspect ratio, so it varies row to row; a
 * fixed-size window (e.g. "row 1's count, applied to every row") would cut
 * a differently-sized row's tail into the next chunk, which isn't
 * necessarily mounted yet — visually truncating that row (missing tiles,
 * unfilled row width) until the user scrolls further. Grouping by the
 * boxes' own `top` transitions can't split a row, by construction.
 */
export function splitIntoRows(boxes: (PhotoBox | undefined)[], startIndex: number, count: number): { start: number; count: number }[] {
	if (count === 0) {
		return [];
	}
	const rows: { start: number; count: number }[] = [];
	let rowStart = startIndex;
	let rowTop = boxes[startIndex]?.top ?? 0;
	for (let i = startIndex + 1; i < startIndex + count; i++) {
		const top = boxes[i]?.top ?? 0;
		if (Math.abs(top - rowTop) > 0.5) {
			rows.push({ start: rowStart, count: i - rowStart });
			rowStart = i;
			rowTop = top;
		}
	}
	rows.push({ start: rowStart, count: startIndex + count - rowStart });
	return rows;
}

/**
 * Groups the already-known, already-positioned box list into one virtualizer
 * chunk per real visual row (FR-065-08, `splitIntoRows()`) — a pure
 * virtualization bookkeeping unit, unrelated to any layout concept, which is
 * what lets a row-indexed virtualizer (`useWindowVirtualizer`) drive
 * `masonry`/`grid`'s genuinely non-row-uniform geometry (Decision Card
 * Q-065-04). Never splits a bucket's header away from at least its first
 * tile.
 */
export function buildPhotoChunks(
	boxes: PhotoBox[],
	buckets: AlbumBucketBoundary[],
	showHeaders: boolean,
	headerTops: PhotoHeaderTop[],
): PhotoChunk[] {
	const chunks: PhotoChunk[] = [];

	buckets.forEach((bucket, bucketIdx) => {
		const headerInfo = showHeaders ? headerTops[bucketIdx] : undefined;

		if (bucket.count === 0) {
			if (headerInfo !== undefined) {
				chunks.push({
					key: `chunk-${bucket.bucketId}-header-only`,
					top: headerInfo.top,
					size: HEADER_ROW_HEIGHT,
					tileStart: bucket.startIndex,
					tileCount: 0,
					header: { label: headerInfo.label, bucketId: headerInfo.bucketId },
				});
			}
			return;
		}

		const rows = splitIntoRows(boxes, bucket.startIndex, bucket.count);
		rows.forEach((row, rowIdx) => {
			const isFirstRowOfBucket = rowIdx === 0;
			const firstBox = boxes[row.start];
			const lastBox = boxes[row.start + row.count - 1];
			const chunkTop = isFirstRowOfBucket && headerInfo !== undefined ? headerInfo.top : (firstBox?.top ?? 0);
			const contentBottom = lastBox !== undefined ? lastBox.top + lastBox.height : chunkTop;

			chunks.push({
				key: `chunk-${bucket.bucketId}-${row.start}`,
				top: chunkTop,
				size: contentBottom - chunkTop,
				tileStart: row.start,
				tileCount: row.count,
				header: isFirstRowOfBucket && headerInfo !== undefined ? { label: headerInfo.label, bucketId: headerInfo.bucketId } : null,
			});
		});
	});

	// Each chunk's virtualizer size is the stride to the next chunk's top
	// (folding the inter-bucket gap in), not its own bare content height —
	// mirrors `buildVirtualAlbumRows()`'s `rowHeights` vs `rowContentHeights`
	// distinction, except here every chunk's children are absolutely
	// positioned relative to the chunk's own top regardless, so a shorter
	// "bare" content height would leave harmless empty space, not a visual
	// stretch bug — using the stride directly is simplest.
	for (let i = 0; i < chunks.length; i++) {
		const next = chunks[i + 1];
		if (next !== undefined) {
			chunks[i].size = Math.max(chunks[i].size, next.top - chunks[i].top);
		}
	}

	return chunks;
}

export type PositionedPhoto = { photo: App.Http.Resources.Models.PhotoResource; box: PhotoBox };

/**
 * Shared by `PhotoGridVirtual.vue` (rendering) and `dragAndSelect.ts`'s
 * `getPhotoBoxesV3()` (hit-testing) so the rating-filter-aware boundary
 * recompute (FR-065-19 — discovered necessity, no Feature 063 precedent)
 * exists in exactly one place. Reuses the already-generic
 * `filterBucketedTiles()` over `{photo, ratio}` pairs so the photo array and
 * its parallel ratio array are filtered/re-chunked in lockstep, never
 * separately (which could desync).
 */
export function computeVisiblePhotoLayout(
	mode: PhotoLayoutMode,
	photos: App.Http.Resources.Models.PhotoResource[],
	ratios: number[],
	boundaries: AlbumBucketBoundary[] | null,
	bucketable: boolean,
	ratingFilterActive: boolean,
	filteredPhotoIds: Set<string> | null,
	containerWidth: number,
	target: number,
	gap: number,
): { positioned: PositionedPhoto[]; totalHeight: number; headerTops: PhotoHeaderTop[]; boundaries: AlbumBucketBoundary[] } {
	const effectiveBoundaries: AlbumBucketBoundary[] = bucketable
		? (boundaries ?? [])
		: [{ bucketId: "all", label: "", startIndex: 0, count: photos.length }];
	const pairs = photos.map((photo, i) => ({ photo, ratio: ratios[i] ?? 1 }));

	// Headers still render when a rating filter is active (FR-065-19) — only
	// the per-bucket *counts* change, re-scanned from the filtered array's
	// own sequence via `filterBucketedTiles()` rather than reusing the
	// original (pre-filter) counts; a bucket filtered down to zero photos is
	// dropped entirely (that function's own existing behavior, matching
	// S-065-15).
	const showHeaders = bucketable;
	const { tiles: visiblePairs, boundaries: visibleBoundaries } =
		ratingFilterActive && filteredPhotoIds !== null
			? filterBucketedTiles(pairs, effectiveBoundaries, (pair) => filteredPhotoIds.has(pair.photo.id))
			: { tiles: pairs, boundaries: effectiveBoundaries };

	const layout = computePhotoLayout(
		mode,
		visiblePairs.map((p) => p.ratio),
		visibleBoundaries,
		showHeaders,
		containerWidth,
		target,
		gap,
	);

	const positioned: PositionedPhoto[] = visiblePairs.map((p, i) => ({ photo: p.photo, box: layout.boxes[i] }));
	return { positioned, totalHeight: layout.totalHeight, headerTops: layout.headerTops, boundaries: visibleBoundaries };
}

// ---------------------------------------------------------------------------
// Timeline (Feature 066) — incremental, bucket-windowed layout.
//
// `computePhotoLayout()`/`computeVisiblePhotoLayout()` above both assume the
// WHOLE scope's `ratios` are already loaded (one dense, index-aligned array).
// Timeline's `tiles`/`ratios` (`TimelineState.ts`) are append-only into a
// flat array PRE-SIZED from the `buckets` tier's own known per-bucket counts
// (FR-066-11) — a bucket whose window hasn't resolved yet leaves a hole
// (`undefined`) at its reserved slots, not a shorter array. The functions
// below are the mid-load-safe counterparts: a not-yet-loaded bucket gets a
// placeholder height from the SAME WASM primitive fed uniform `1.0`-ratio
// input (NFR-066-05, layout-mode-correct — justified/masonry/square/grid all
// pack `1.0`-ratio input consistently, so this is never an arbitrary guess),
// and only the one bucket whose ratio source actually changed
// (placeholder→real) is ever recomputed (T-066-24) — every other bucket's
// cache entry is reused untouched.
// ---------------------------------------------------------------------------

/** One cached per-bucket layout result, keyed by a signature over everything that could invalidate it (mode/width/target/gap/loaded-state/count) — a signature mismatch is what triggers recomputation for exactly that one bucket (T-066-24). */
export type TimelineLayoutCacheEntry = {
	/** `null` for a not-yet-loaded bucket — nothing to render yet, only `containerHeight` (the placeholder height) matters. */
	boxes: PhotoBox[] | null;
	containerHeight: number;
	signature: string;
};

export type TimelineBucketPixelLayout = {
	bucketId: string;
	label: string;
	startIndex: number;
	count: number;
	loaded: boolean;
	/** Absolute top, same coordinate space as the returned `boxes` — includes this bucket's own header row when `showHeaders` is true. */
	top: number;
	/** This bucket's own stride (header + content), NOT including the inter-bucket gap already folded into `top`'s running total. */
	height: number;
};

/**
 * Timeline's counterpart to `computePhotoLayout()` (DO-066-06,
 * `computeTimelineBucketLayout()`'s sibling in `albumBucketBoundaries.ts`
 * handles the count-only boundary math; this one adds real pixel geometry).
 * A cheap O(#buckets) prefix-sum pass over `boundaries` — each bucket's own
 * `{boxes, containerHeight}` comes from `cache` when its signature still
 * matches (T-066-24), or is computed fresh (real layout for a loaded bucket,
 * uniform-`1.0`-ratio placeholder layout for one that isn't, T-066-25) and
 * written back into `cache` otherwise. Never re-runs the WASM primitive for
 * a bucket whose own inputs haven't changed.
 */
export function computeTimelinePhotoLayout(
	mode: PhotoLayoutMode,
	boundaries: TimelineBucketBoundary[],
	ratios: (number | undefined)[],
	loadedBucketIds: ReadonlySet<string>,
	cache: Map<string, TimelineLayoutCacheEntry>,
	showHeaders: boolean,
	containerWidth: number,
	target: number,
	gap: number,
): { boxes: (PhotoBox | undefined)[]; totalHeight: number; headerTops: PhotoHeaderTop[]; buckets: TimelineBucketPixelLayout[] } {
	const boxes: (PhotoBox | undefined)[] = new Array(ratios.length);
	const headerTops: PhotoHeaderTop[] = [];
	const bucketLayouts: TimelineBucketPixelLayout[] = [];
	let runningTop = 0;

	for (const bucket of boundaries) {
		const bucketTop = runningTop;
		const loaded = loadedBucketIds.has(bucket.bucketId);

		if (showHeaders) {
			headerTops.push({ top: runningTop, label: bucket.label, bucketId: bucket.bucketId });
			runningTop += HEADER_ROW_HEIGHT + gap;
		}

		const signature = `${mode}|${containerWidth}|${target}|${gap}|${loaded ? "real" : "placeholder"}|${bucket.count}`;
		let cached = cache.get(bucket.bucketId);
		if (cached === undefined || cached.signature !== signature) {
			if (loaded) {
				const bucketRatios: number[] = [];
				for (let i = bucket.startIndex; i < bucket.startIndex + bucket.count; i++) {
					bucketRatios.push(ratios[i] ?? 1);
				}
				const { boxes: bucketBoxes, containerHeight } = computeBucketLayout(mode, bucketRatios, containerWidth, target, gap);
				cached = { boxes: bucketBoxes, containerHeight, signature };
			} else {
				const placeholderRatios: number[] = new Array(bucket.count).fill(1);
				const { containerHeight } = computeBucketLayout(mode, placeholderRatios, containerWidth, target, gap);
				cached = { boxes: null, containerHeight, signature };
			}
			cache.set(bucket.bucketId, cached);
		}

		if (cached.boxes !== null) {
			for (let i = 0; i < cached.boxes.length; i++) {
				const b = cached.boxes[i];
				boxes[bucket.startIndex + i] = { top: b.top + runningTop, left: b.left, width: b.width, height: b.height };
			}
		}
		runningTop += cached.containerHeight + gap;

		bucketLayouts.push({
			bucketId: bucket.bucketId,
			label: bucket.label,
			startIndex: bucket.startIndex,
			count: bucket.count,
			loaded,
			top: bucketTop,
			height: runningTop - gap - bucketTop,
		});
	}

	const totalHeight = boundaries.length > 0 ? Math.max(0, runningTop - gap) : 0;
	return { boxes, totalHeight, headerTops, buckets: bucketLayouts };
}

export type TimelineChunk = PhotoChunk & {
	/** `true` for a not-yet-loaded bucket's single placeholder chunk — `tileCount` is always `0` in that case, nothing to mount. */
	placeholder: boolean;
	bucketId: string;
};

/**
 * Timeline's counterpart to `buildPhotoChunks()`. A loaded bucket is chunked
 * identically (one virtualizer row per real visual row, `splitIntoRows()`).
 * A not-yet-loaded bucket collapses to exactly ONE chunk spanning its own
 * placeholder height (header included, when shown) — `tileCount: 0`, so the
 * virtualizer never tries to mount tiles for a slot `tiles`/`ratios` hasn't
 * resolved yet; it reflows into the real per-row chunks the moment
 * `computeTimelinePhotoLayout()` reports that bucket as `loaded`.
 */
export function buildTimelineChunks(boxes: (PhotoBox | undefined)[], buckets: TimelineBucketPixelLayout[], showHeaders: boolean): TimelineChunk[] {
	const chunks: TimelineChunk[] = [];

	buckets.forEach((bucket) => {
		if (!bucket.loaded || bucket.count === 0) {
			chunks.push({
				key: `chunk-${bucket.bucketId}-placeholder`,
				top: bucket.top,
				size: bucket.height,
				tileStart: bucket.startIndex,
				tileCount: 0,
				header: showHeaders ? { label: bucket.label, bucketId: bucket.bucketId } : null,
				placeholder: true,
				bucketId: bucket.bucketId,
			});
			return;
		}

		const rows = splitIntoRows(boxes, bucket.startIndex, bucket.count);
		rows.forEach((row, rowIdx) => {
			const isFirstRowOfBucket = rowIdx === 0;
			const firstBox = boxes[row.start];
			const lastBox = boxes[row.start + row.count - 1];
			const chunkTop = isFirstRowOfBucket && showHeaders ? bucket.top : (firstBox?.top ?? 0);
			const contentBottom = lastBox !== undefined ? lastBox.top + lastBox.height : chunkTop;

			chunks.push({
				key: `chunk-${bucket.bucketId}-${row.start}`,
				top: chunkTop,
				size: contentBottom - chunkTop,
				tileStart: row.start,
				tileCount: row.count,
				header: isFirstRowOfBucket && showHeaders ? { label: bucket.label, bucketId: bucket.bucketId } : null,
				placeholder: false,
				bucketId: bucket.bucketId,
			});
		});
	});

	// Same stride-folding pass as `buildPhotoChunks()` — see there.
	for (let i = 0; i < chunks.length; i++) {
		const next = chunks[i + 1];
		if (next !== undefined) {
			chunks[i].size = Math.max(chunks[i].size, next.top - chunks[i].top);
		}
	}

	return chunks;
}
