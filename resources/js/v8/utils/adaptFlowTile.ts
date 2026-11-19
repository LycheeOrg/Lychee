type FlowListResource = App.Http.Resources.V3.FlowListResource;

/**
 * One album-indexed row of Feature 068's `GET /api/v3/Flow` SoA response
 * (FR-068-01), de-parallelized into a per-card view object — mirrors
 * `adaptPhotoTile()`'s role for the photo-listing SoA tiers, but built fresh
 * for `AlbumCardV3.vue` rather than synthesized to fit an existing
 * `FlowItemResource`-shaped consumer (there is none on the v3 path; the v2
 * `FlowItemResource` stays exclusively on the v2 rendering path).
 */
export type AdaptedFlowTile = {
	id: string;
	title: string;
	description: string;
	coverId: string | null;
	ownerName: string | null;
	isNsfw: boolean;
	numPhotos: number;
	numChildren: number;
	minMaxText: string | null;
	publishedCreatedAt: string;
	diffPublishedCreatedAt: string;
	statistics: App.Http.Resources.Models.AlbumStatisticsResource | null;
};

export function adaptFlowTile(i: number, data: FlowListResource): AdaptedFlowTile {
	return {
		id: data.ids[i],
		title: data.titles[i],
		description: data.descriptions[i],
		coverId: data.cover_ids[i],
		ownerName: data.owner_names[i],
		isNsfw: data.is_nsfws[i],
		numPhotos: data.num_photos[i],
		numChildren: data.num_children[i],
		minMaxText: data.min_max_texts[i],
		publishedCreatedAt: data.published_created_ats[i],
		diffPublishedCreatedAt: data.diff_published_created_ats[i],
		statistics: data.statistics[i],
	};
}

export function adaptFlowTiles(data: FlowListResource): AdaptedFlowTile[] {
	return data.ids.map((_, i) => adaptFlowTile(i, data));
}
