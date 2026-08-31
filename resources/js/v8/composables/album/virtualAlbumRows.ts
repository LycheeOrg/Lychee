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
	/** Parallel to `rows` — each row's height in px. */
	rowHeights: number[];
	/** Row/column → absolute box (FR-062-11): every child's box, mounted or not. O(1) per call. */
	getTileBox: (childIndex: number) => TileBox;
};

/** A header row's fixed height in px — shared with the sticky-header overlay math (grid/list panels). */
export const HEADER_ROW_HEIGHT = 40;

/** List-view row's fixed height in px (FR-062-10) — shared by AlbumListViewVirtual.vue and the drag-select geometry reimplementation. */
export const LIST_ROW_HEIGHT = 40;

/**
 * Turns `(childIds[], buckets, itemsPerRow, tileWidth, aspectRatioNumber)`
 * into a flattened virtualizer row list (header rows + tile rows) and a
 * tile-geometry lookup (DO-062-02) — shared by the grid virtual list
 * (FR-062-07), the list-view virtual list (FR-062-10, fixed row height —
 * not this height math), and the reimplemented drag-select geometry test
 * (FR-062-11).
 *
 * `getTileBox()` is O(1) per call (offsets precomputed once here, not
 * re-walked per lookup) — drag-select tests every child's box against the
 * drag rectangle, mounted or not, so this must stay cheap at the 7,000+-child
 * scale this feature targets.
 *
 * @param childIds   Flat, already-ordered/grouped child ids (tier 2's own
 *                   order, FR-061-26 — used only for row keys, never re-sorted).
 * @param buckets    Bucket-boundary metadata (FR-062-02). Pass a single
 *                   `{bucketId: "all", label: "", startIndex: 0, count: childIds.length}`
 *                   entry with `showHeaders: false` for the `bucketable: false`
 *                   (FR-062-09) or count-mismatch (FR-062-02 Failure path)
 *                   fallback — both collapse to the same flat rendering path.
 * @param showHeaders Whether to emit header rows at all (`false` for the
 *                   flat-fallback case above).
 * @param itemsPerRow Tiles per row (FR-062-14) — degenerate `1` for list view.
 * @param tileWidth   Analytically-computed tile width in px (FR-062-14).
 * @param aspectRatioNumber Parent album's aspect ratio as width/height (FR-062-14).
 * @param gap         Analytically-computed gap in px between tiles/rows (FR-062-14).
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
	const rowStride = tileHeight + gap;

	const rows: VirtualAlbumRow[] = [];
	const rowHeights: number[] = [];
	// Per-child (by flat index) absolute top/col, precomputed once below —
	// getTileBox() is then a plain array read, O(1).
	const childTop: number[] = new Array(childIds.length);
	const childCol: number[] = new Array(childIds.length);

	let runningTop = 0;

	for (const bucket of buckets) {
		if (showHeaders) {
			rows.push({ type: "header", key: `header-${bucket.bucketId}`, bucketId: bucket.bucketId, label: bucket.label });
			rowHeights.push(headerHeight);
			runningTop += headerHeight;
		}

		const tileRowCount = Math.ceil(bucket.count / itemsPerRow);
		for (let r = 0; r < tileRowCount; r++) {
			const rowStartIndex = bucket.startIndex + r * itemsPerRow;
			const rowCount = Math.min(itemsPerRow, bucket.count - r * itemsPerRow);

			for (let c = 0; c < rowCount; c++) {
				childTop[rowStartIndex + c] = runningTop;
				childCol[rowStartIndex + c] = c;
			}

			rows.push({
				type: "tiles",
				key: `tiles-${childIds[rowStartIndex] ?? `${bucket.bucketId}-${r}`}`,
				startIndex: rowStartIndex,
				count: rowCount,
			});
			rowHeights.push(tileHeight);
			runningTop += rowStride;
		}
	}

	function getTileBox(childIndex: number): TileBox {
		const top = childTop[childIndex];
		const col = childCol[childIndex];
		if (top === undefined || col === undefined) {
			return { top: 0, left: 0, width: tileWidth, height: tileHeight };
		}
		return { top: top, left: col * columnStride, width: tileWidth, height: tileHeight };
	}

	return { rows, rowHeights, getTileBox };
}
