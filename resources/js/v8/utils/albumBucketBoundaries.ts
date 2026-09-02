export type AlbumBucketBoundary = {
	bucketId: string;
	label: string;
	startIndex: number;
	count: number;
};

/**
 * Positional walk (FR-063-02) — not a join or sort: tier 2's children are
 * already ordered to match tier 1's bucket order and per-bucket count
 * exactly (Feature 061 FR-061-26), so this simply slices tier 2's flat
 * array into consecutive runs of `counts[0]`, `counts[1]`, ... children
 * each, labeling each run with the corresponding `bucket_ids[i]`/`labels[i]`.
 * Never reads tier 2's own `bucket_ids` — the backend's ordering guarantee
 * makes that unnecessary.
 *
 * @param buckets      Tier 1 response (`AlbumBucketResource`).
 * @param childrenCount Tier 2's actual child count (`childrenV3.ids.length`).
 * @returns Boundary metadata, or `null` if `sum(counts) !== childrenCount`
 *          (should not happen per FR-061-17's correlation guarantee — only
 *          a same-session cross-request race could cause it) — the caller
 *          falls back to one single unbucketed section in that case, the
 *          same flat rendering path FR-063-09 uses for `bucketable: false`.
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
		boundaries.push({
			bucketId: buckets.bucket_ids[i],
			label: buckets.labels[i],
			startIndex: startIndex,
			count: count,
		});
		startIndex += count;
	}

	return boundaries;
}
