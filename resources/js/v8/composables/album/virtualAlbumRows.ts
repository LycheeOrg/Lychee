import type { AlbumBucketBoundary } from "@/v8/utils/albumBucketBoundaries";

export type VirtualHeaderRow = {
	type: "header";
	key: string;
	bucketId: string;
	label: string;
};

export type VirtualTileRow = {
	type: "tiles";
	key: string;
	/** Index into the flat children array of the first tile in this row. */
	startIndex: number;
	/** Number of tiles in this row (may be less than `itemsPerRow` for a bucket's last row). */
	count: number;
};

export type VirtualAlbumRow = VirtualHeaderRow | VirtualTileRow;

export type TileBox = {
	top: number;
	left: number;
	width: number;
	height: number;
};

export type VirtualAlbumRowsResult = {
	rows: VirtualAlbumRow[];
	/** Parallel to `rows` — each row's height in px, including the vertical gap to the next row (0 on the last row). This is what the virtualizer's `estimateSize` must return so rows stack with the same gap the horizontal flex layout applies between tiles. */
	rowHeights: number[];
	/** Parallel to `rows` — each row's own content height in px, excluding any trailing gap. Use this for the rendered content's height so it doesn't stretch into the gap reserved by `rowHeights`. */
	rowContentHeights: number[];
	/** Row/column → absolute box: every child's box, mounted or not. O(1) per call. */
	getTileBox: (childIndex: number) => TileBox;
};

/** A header row's fixed height in px — shared with the sticky-header overlay math (grid/list panels). */
export const HEADER_ROW_HEIGHT = 40;

/** List-view row's fixed height in px — shared by AlbumListViewVirtual.vue and the drag-select geometry reimplementation. */
export const LIST_ROW_HEIGHT = 40;

/**
 * Turns `(childIds[], buckets, itemsPerRow, tileWidth, aspectRatioNumber)`
 * into a flattened virtualizer row list (header rows + tile rows) and a
 * tile-geometry lookup — shared by the grid virtual list, the list-view
 * virtual list (fixed row height — not this height math), and the
 * reimplemented drag-select geometry test.
 *
 * `getTileBox()` is O(1) per call (offsets precomputed once here, not
 * re-walked per lookup) — drag-select tests every child's box against the
 * drag rectangle, mounted or not, so this must stay cheap at the 7,000+-child
 * scale this feature targets.
 *
 * @param childIds   Flat, already-ordered/grouped child ids (tier 2's own
 *                   order — used only for row keys, never re-sorted).
 * @param buckets    Bucket-boundary metadata. Pass a single
 *                   `{bucketId: "all", label: "", startIndex: 0, count: childIds.length}`
 *                   entry with `showHeaders: false` for the `bucketable: false`
 *                   or count-mismatch fallback — both collapse to the same
 *                   flat rendering path.
 * @param showHeaders Whether to emit header rows at all (`false` for the
 *                   flat-fallback case above).
 * @param itemsPerRow Tiles per row — degenerate `1` for list view.
 * @param tileWidth   Analytically-computed tile width in px.
 * @param aspectRatioNumber Parent album's aspect ratio as width/height.
 * @param gap         Analytically-computed gap in px between tiles/rows.
 *                    Applied both horizontally (by the caller's own flex
 *                    `gap` styling, between tiles in a row) and vertically —
 *                    baked into `rowHeights` here, uniformly between every
 *                    pair of consecutive rows (header or tile alike).
 */
export function buildVirtualAlbumRows(
	childIds: string[],
	buckets: AlbumBucketBoundary[],
	showHeaders: boolean,
	itemsPerRow: number,
	tileWidth: number,
	aspectRatioNumber: number,
	gap: number,
): VirtualAlbumRowsResult {
	const tileHeight = aspectRatioNumber > 0 ? tileWidth / aspectRatioNumber : tileWidth;
	const headerHeight = HEADER_ROW_HEIGHT;
	const columnStride = tileWidth + gap;

	const rows: VirtualAlbumRow[] = [];
	// Each row's own content height (no trailing gap) — gap is folded into
	// `rowHeights` afterwards, once every row's true height is known.
	const rowContentHeights: number[] = [];
	// Per-child (by flat index) absolute top/col, precomputed once below —
	// getTileBox() is then a plain array read, O(1).
	const childTop: number[] = new Array(childIds.length);
	const childCol: number[] = new Array(childIds.length);

	// `runningTop` advances by each row's own height plus the gap uniformly
	// (header-to-tile, tile-to-tile, bucket-to-bucket alike) — matching the
	// non-virtual grid, whose rows/headers are all siblings in one
	// `flex-wrap` container with a single `gap` applied between every pair.
	let runningTop = 0;

	for (const bucket of buckets) {
		if (showHeaders) {
			rows.push({ type: "header", key: `header-${bucket.bucketId}`, bucketId: bucket.bucketId, label: bucket.label });
			rowContentHeights.push(headerHeight);
			runningTop += headerHeight + gap;
		}

		const tileRowCount = Math.ceil(bucket.count / itemsPerRow);
		for (let r = 0; r < tileRowCount; r++) {
			const rowStartIndex = bucket.startIndex + r * itemsPerRow;
			const rowCount = Math.min(itemsPerRow, bucket.count - r * itemsPerRow);
			const rowTop = runningTop;

			for (let c = 0; c < rowCount; c++) {
				childTop[rowStartIndex + c] = rowTop;
				childCol[rowStartIndex + c] = c;
			}

			rows.push({
				type: "tiles",
				key: `tiles-${childIds[rowStartIndex] ?? `${bucket.bucketId}-${r}`}`,
				startIndex: rowStartIndex,
				count: rowCount,
			});
			rowContentHeights.push(tileHeight);
			runningTop += tileHeight + gap;
		}
	}

	// The virtualizer needs each row's *stride* (content height + gap to the
	// next row), not its bare content height, so consecutive absolutely
	// positioned rows land `gap`px apart — but the last row must not reserve
	// a trailing gap (there's no row after it to space out from).
	const rowHeights = rowContentHeights.map((h, i) => h + (i === rowContentHeights.length - 1 ? 0 : gap));

	function getTileBox(childIndex: number): TileBox {
		const top = childTop[childIndex];
		const col = childCol[childIndex];
		if (top === undefined || col === undefined) {
			return { top: 0, left: 0, width: tileWidth, height: tileHeight };
		}
		return { top: top, left: col * columnStride, width: tileWidth, height: tileHeight };
	}

	return { rows, rowHeights, rowContentHeights, getTileBox };
}
