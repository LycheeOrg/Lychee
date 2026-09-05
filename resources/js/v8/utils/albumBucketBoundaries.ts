export type AlbumBucketBoundary = {
	bucketId: string;
	label: string;
	startIndex: number;
	count: number;
};

/**
 * Positional walk — not a join or sort: tier 2's children are
 * already ordered to match tier 1's bucket order and per-bucket count
 * exactly, so this simply slices tier 2's flat
 * array into consecutive runs of `counts[0]`, `counts[1]`, ... children
 * each, labeling each run with the corresponding `bucket_ids[i]`/`labels[i]`.
 * Never reads tier 2's own `bucket_ids` — the backend's ordering guarantee
 * makes that unnecessary.
 *
 * @param buckets      Tier 1 response (`AlbumBucketResource`).
 * @param childrenCount Tier 2's actual child count (`childrenV3.ids.length`).
 * @returns Boundary metadata, or `null` if `sum(counts) !== childrenCount`
 *          (should not happen per the backend's correlation guarantee — only
 *          a same-session cross-request race could cause it) — the caller
 *          falls back to one single unbucketed section in that case, the
 *          same flat rendering path used for `bucketable: false`.
 */
export function computeBucketBoundaries(buckets: App.Http.Resources.V3.AlbumBucketResource, childrenCount: number): AlbumBucketBoundary[] | null {
	const total = buckets.counts.reduce((sum, count) => sum + count, 0);
	if (total !== childrenCount) {
		return null;
	}

	const boundaries: AlbumBucketBoundary[] = [];
	let startIndex = 0;
	for (let i = 0; i < buckets.bucket_ids.length; i++) {
		const count = buckets.counts[i];
		const label = buckets.labels[i];
		// Adjacent buckets sharing a label are merged into one — e.g. the
		// root "shared" scope buckets by owner_id, but a guest can never see
		// owner names and gets every label hardcoded "unknown"
		// (`AlbumRootController::querySharedBuckets()`), which would
		// otherwise render one repeated "unknown" header per distinct owner.
		// Safe because tier 2's children are already ordered to match tier
		// 1's bucket order, so same-label runs are always contiguous.
		const previous = boundaries[boundaries.length - 1];
		if (previous !== undefined && previous.label === label) {
			previous.count += count;
		} else {
			boundaries.push({
				bucketId: buckets.bucket_ids[i],
				label: label,
				startIndex: startIndex,
				count: count,
			});
		}
		startIndex += count;
	}

	return boundaries;
}

/**
 * Drops tiles a predicate rejects (NSFW tiles when hidden) from both the
 * flat tile array and the bucket boundaries, recomputing each surviving
 * bucket's `count`/`startIndex` against the filtered array — rather than
 * just skipping a hidden tile's own rendered box in place. Filtering after
 * `buildVirtualAlbumRows()` has already chunked bucket ranges into
 * itemsPerRow-sized rows leaves a gap at the *end* of whichever row lost a
 * tile (nothing shifts up from the row below to fill it, since row
 * boundaries were fixed against the original, unfiltered counts) — so the
 * filtering has to happen first, before row-chunking ever sees the counts.
 * A bucket that loses every one of its tiles is dropped entirely (so its
 * header doesn't render over zero rows).
 */
export function filterBucketedTiles<T>(
	tiles: T[],
	boundaries: AlbumBucketBoundary[],
	isVisible: (tile: T) => boolean,
): { tiles: T[]; boundaries: AlbumBucketBoundary[] } {
	const filteredTiles: T[] = [];
	const filteredBoundaries: AlbumBucketBoundary[] = [];

	for (const bucket of boundaries) {
		const startIndex = filteredTiles.length;
		for (let i = bucket.startIndex; i < bucket.startIndex + bucket.count; i++) {
			const tile = tiles[i];
			if (tile !== undefined && isVisible(tile)) {
				filteredTiles.push(tile);
			}
		}
		const count = filteredTiles.length - startIndex;
		if (count > 0) {
			filteredBoundaries.push({ bucketId: bucket.bucketId, label: bucket.label, startIndex, count });
		}
	}

	return { tiles: filteredTiles, boundaries: filteredBoundaries };
}
