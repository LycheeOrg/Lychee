import { defineStore } from "pinia";
import { PhotosStore, usePhotosStore } from "./PhotosState";
import { useLayoutStore } from "./LayoutState";
import { useLycheeStateStore } from "./LycheeState";
import TimelineService from "@/services/timeline-service";
import PhotoChildrenV3Service, { type PhotoBucketResource } from "@/services/photo-children-v3-service";
import { adaptPhotoTile, mergePhotoDetail } from "@/v8/utils/adaptPhotoTile";
import { computeTimelineBucketLayout, type TimelineBucketBoundary } from "@/v8/utils/albumBucketBoundaries";
import { AxiosResponse } from "axios";
import { useSplitter } from "@/composables/album/splitter";

const { spliter, merge } = useSplitter();

export type TimelineStore = ReturnType<typeof useTimelineStore>;

function _parseResponse(
	response: AxiosResponse<App.Http.Resources.Timeline.TimelineResource>,
	timelineState: TimelineStore,
	photosState: PhotosStore,
) {
	_processPhotos(response.data.photos, photosState);
	timelineState.lastPage = response.data.last_page;
	timelineState.maxPage = response.data.current_page;
	timelineState.minPage = response.data.current_page;
	timelineState.isLoading = false;
}

function _processPhotos(photos_data: App.Http.Resources.Models.PhotoResource[], photosState: PhotosStore) {
	photosState.photosTimeline = spliter(
		photos_data,
		(p: App.Http.Resources.Models.PhotoResource) => p.timeline?.time_date ?? "",
		(p: App.Http.Resources.Models.PhotoResource) => p.timeline?.format ?? "Others",
	);
	photosState.photos = merge(photosState.photosTimeline);
}

/**
 * `App.Http.Resources.Models.PhotoResource | undefined` — the flat, v3
 * `tiles` array (DO-066-05) is pre-sized to the whole library's photo count
 * the moment `buckets` resolves (FR-066-11), but a slot only holds a real
 * tile once its own bucket's `ratios` window has been fetched;
 * `undefined` marks a not-yet-loaded slot. Mirrors `ratiosV3` below
 * (index-aligned, same holes) — `PhotoGridVirtual.vue`'s Timeline layout
 * path is the only consumer of either array directly; `photosStore.photos`
 * only ever holds the *compacted* (hole-free) subset (see
 * `_syncPhotosStoreV3()`), since it's strictly typed `PhotoResource[]`
 * (`adaptPhotoTile.ts`'s own documented convention).
 */
type TimelineTile = App.Http.Resources.Models.PhotoResource | undefined;

export const useTimelineStore = defineStore("timeline-store", {
	state: () => ({
		isLoading: false,
		minPage: 0,
		maxPage: 0,
		lastPage: 0,

		isTimelineEnabled: undefined as undefined | boolean,
		layout: undefined as undefined | App.Enum.PhotoLayoutType,
		rootConfig: undefined as undefined | App.Http.Resources.GalleryConfigs.RootConfig,
		rootRights: undefined as undefined | App.Http.Resources.Rights.RootAlbumRightsResource,

		dates: [] as App.Http.Resources.Models.Utils.TimelineData[],

		// --- Feature 066 (v3, SoA) state below. The v2 state/actions above
		// are left completely untouched — both stay reachable from this same
		// store, dispatched between by `isTimelineSoaActive` (mirrors
		// `AlbumState.ts`'s own `isPhotoSoaActive`/v2-v3 coexistence
		// pattern; see plan.md I10's own reasoning for keeping both in one
		// file rather than a forked store). ---

		/** Whole-library `buckets` tier (FR-066-05) — fetched once, eagerly, on first mount (`ensureBucketsV3()`), never paginated. */
		bucketsV3: undefined as PhotoBucketResource | undefined,
		/** `computeTimelineBucketLayout()` (DO-066-06) applied to `bucketsV3` — valid the instant `bucketsV3` resolves, independent of how many bucket windows have loaded. */
		boundariesV3: [] as TimelineBucketBoundary[],
		/** Flat, append-only, pre-sized to `sum(bucketsV3.counts)` — see `TimelineTile`'s own doc comment. */
		tilesV3: [] as TimelineTile[],
		/** Index-aligned with `tilesV3` — `undefined` for a not-yet-loaded slot, consumed by `computeTimelinePhotoLayout()`'s placeholder/real layout split. */
		ratiosV3: [] as (number | undefined)[],
		/** `bucketId -> true` once that bucket's `ratios` window has resolved — checked by `requestBucketWindow()`'s dedup. */
		loadedBucketsV3: {} as Record<string, boolean>,
		/** `bucketId -> true` while that bucket's `ratios` request is in flight — checked by `requestBucketWindow()`'s dedup (no duplicate concurrent fetch for the same bucket). */
		loadingBucketsV3: {} as Record<string, boolean>,
		/** Mirrors `AlbumState.ts`'s own `photoDetailsResolvedIds` — dedupes `loadPhotoDetailsV3()` calls for a photo already resolved this session. */
		photoDetailsResolvedIdsV3: {} as Record<string, boolean>,
		isLoadingV3: false as boolean,
	}),
	actions: {
		load(): Promise<void> {
			const layoutState = useLayoutStore();

			if (this.layout !== undefined) {
				layoutState.layout = this.layout;
				return Promise.resolve();
			}
			return TimelineService.init().then((response) => {
				this.isTimelineEnabled = response.data.is_timeline_page_enabled;
				this.layout = response.data.photo_layout;
				this.rootConfig = response.data.config;
				this.rootRights = response.data.rights;
				layoutState.layout = this.layout;
			});
		},
		loadLess(): Promise<void> {
			const photosState = usePhotosStore();

			const prevPage = this.minPage - 1;
			if (prevPage < 1) {
				return Promise.resolve();
			}
			this.isLoading = true;
			return TimelineService.timeline(prevPage)
				.then((response) => {
					this.minPage -= 1;
					photosState.photos.unshift(...response.data.photos);
					_processPhotos(photosState.photos, photosState);
				})
				.finally(() => {
					this.isLoading = false;
				});
		},
		loadMore(): Promise<void> {
			const photosState = usePhotosStore();

			const nextPage = this.maxPage + 1;
			if (this.lastPage !== 0 && nextPage > this.lastPage) {
				return Promise.resolve();
			}
			this.isLoading = true;
			return TimelineService.timeline(nextPage)
				.then((response) => {
					this.maxPage = nextPage;
					photosState.photos.push(...response.data.photos);
					_processPhotos(photosState.photos, photosState);
					this.lastPage = response.data.last_page;
				})
				.finally(() => {
					this.isLoading = false;
				});
		},
		initialLoad(date: string, photoId: string | undefined): Promise<void> {
			const photosState = usePhotosStore();
			this.isLoading = true;
			if (photoId) {
				return TimelineService.photoIdedTimeline(photoId)
					.then((data) => _parseResponse(data, this, photosState))
					.finally(() => (this.isLoading = false));
			}

			return TimelineService.datedTimeline(date)
				.then((data) => _parseResponse(data, this, photosState))
				.finally(() => (this.isLoading = false));
		},
		loadDates(): Promise<void> {
			if (this.dates.length > 0) {
				return Promise.resolve();
			}
			return TimelineService.dates().then((response) => {
				this.dates = response.data;
			});
		},

		// --- Feature 066 v3 actions ---

		/** Fully resets the v3 (SoA) state — mirrors `AlbumState.ts.reset()`'s own discipline, called whenever the Timeline panel is torn down/remounted. Deliberately leaves v2 state (`dates`/pages/…) alone — `reset()`-ing that is unnecessary here since v2 and v3 never run concurrently. */
		resetV3(): void {
			this.bucketsV3 = undefined;
			this.boundariesV3 = [];
			this.tilesV3 = [];
			this.ratiosV3 = [];
			this.loadedBucketsV3 = {};
			this.loadingBucketsV3 = {};
			this.photoDetailsResolvedIdsV3 = {};
			this.isLoadingV3 = false;
		},

		/**
		 * Eager, whole-scope, once-per-visit `buckets` fetch (FR-066-11) —
		 * idempotent, safe to call from multiple entry points (`loadV3()`,
		 * `PhotoGridVirtual.vue`'s own mount hook).
		 */
		async ensureBucketsV3(): Promise<void> {
			if (this.bucketsV3 !== undefined) {
				return;
			}

			const response = await PhotoChildrenV3Service.getBuckets("timeline");
			const buckets = response.data;
			const boundaries = computeTimelineBucketLayout(buckets);
			const total = boundaries.reduce((sum, b) => sum + b.count, 0);

			this.bucketsV3 = buckets;
			this.boundariesV3 = boundaries;
			this.tilesV3 = new Array<TimelineTile>(total).fill(undefined);
			this.ratiosV3 = new Array<number | undefined>(total).fill(undefined);
		},

		/**
		 * Dedupes in-flight/already-loaded buckets (FR-066-11), fetches the
		 * remainder in one `bucket_ids[]`-scoped request, and splices each
		 * returned row into `tilesV3`/`ratiosV3` at its bucket's known
		 * offset. Rows come back sorted by the Timeline's own effective sort
		 * (date DESC, never bucket-id-grouped server-side the way a regular
		 * `Album`'s response is — `QueryPhotoRatios::do()`), but since
		 * buckets are disjoint, non-overlapping date ranges, a single global
		 * date-sorted response is *already* grouped contiguously per bucket
		 * in exactly the same relative order `boundariesV3` itself uses — a
		 * per-bucket write cursor (not a full re-sort) is all that's needed.
		 */
		async requestBucketWindow(bucketIds: string[]): Promise<void> {
			if (this.bucketsV3 === undefined) {
				return;
			}

			const offsetByBucket = new Map(this.boundariesV3.map((b) => [b.bucketId, b]));
			const toFetch = [...new Set(bucketIds)].filter((id) => offsetByBucket.has(id) && !this.loadedBucketsV3[id] && !this.loadingBucketsV3[id]);
			if (toFetch.length === 0) {
				return;
			}

			toFetch.forEach((id) => {
				this.loadingBucketsV3[id] = true;
			});

			try {
				const response = await PhotoChildrenV3Service.getRatios("timeline", { bucketIds: toFetch });
				const ratios = response.data;
				const cursor = new Map<string, number>();

				for (let i = 0; i < ratios.ids.length; i++) {
					const bucketId = ratios.bucket_ids[i];
					const offset = offsetByBucket.get(bucketId);
					if (offset === undefined) {
						// Defensive only — every row's `bucket_ids[i]` should
						// belong to one of the requested buckets (or, for
						// "unknown", a bucket already known from `buckets`).
						continue;
					}
					const written = cursor.get(bucketId) ?? 0;
					if (written >= offset.count) {
						continue;
					}
					cursor.set(bucketId, written + 1);
					const index = offset.startIndex + written;
					this.tilesV3[index] = adaptPhotoTile(i, ratios, "timeline");
					this.ratiosV3[index] = ratios.ratios[i];
				}

				toFetch.forEach((id) => {
					this.loadedBucketsV3[id] = true;
					delete this.loadingBucketsV3[id];
				});
			} catch (error) {
				toFetch.forEach((id) => {
					delete this.loadingBucketsV3[id];
				});
				console.error(error);
				return;
			}

			this._syncPhotosStoreV3();
		},

		/**
		 * Writes the currently-loaded (hole-free, compacted) subset of
		 * `tilesV3` into `photosStore.photos` — the shared field
		 * `PhotoState.ts`'s lightbox/next-previous navigation and
		 * `useSelection()` already read regardless of route. Explicit,
		 * call-site-driven (`[[feedback_no_hooks_explicit_writes]]`) —
		 * called at the end of every `requestBucketWindow()`, never via a
		 * reactive watcher.
		 */
		_syncPhotosStoreV3(): void {
			const photosState = usePhotosStore();
			const compacted = this.tilesV3.filter((t): t is App.Http.Resources.Models.PhotoResource => t !== undefined);
			photosState.setPhotos(compacted, false);
			photosState.rebuildNavigationLinks();
		},

		/**
		 * Resolves a photo id to its bucket via the tiny `photo_ids[]`-scoped
		 * `ratios` lookup (FR-066-13, S-066-07) — reuses the same windowed-
		 * fetch machinery as `requestBucketWindow()` but is never cached
		 * against the bucket-window offsets (a single-photo lookup, thrown
		 * away once the bucket id is known).
		 */
		async resolveBucketForPhotoV3(photoId: string): Promise<string | null> {
			try {
				const response = await PhotoChildrenV3Service.getRatios("timeline", { photoIds: [photoId] });
				return response.data.bucket_ids[0] ?? null;
			} catch (error) {
				console.error(error);
				return null;
			}
		},

		/** The target bucket plus its immediate chronological neighbours (FR-066-13's "± neighbors" eager load). */
		neighborBucketIdsV3(bucketId: string): string[] {
			const index = this.boundariesV3.findIndex((b) => b.bucketId === bucketId);
			if (index === -1) {
				return [bucketId];
			}
			const ids: string[] = [];
			for (let i = Math.max(0, index - 1); i <= Math.min(this.boundariesV3.length - 1, index + 1); i++) {
				ids.push(this.boundariesV3[i].bucketId);
			}
			return ids;
		},

		/**
		 * Flag-on entry point (mirrors v2's own `initialLoad()`+`loadDates()`
		 * pair, called once from `Timeline.vue`'s `refresh()`): ensures
		 * `buckets` is loaded, resolves a `photoId` deep link to its bucket
		 * when given (a bare `/timeline/:date` skips this — the date param
		 * already IS the bucket-id string, FR-066-13), and eagerly loads the
		 * resolved (or first, when no deep link at all) bucket window ± its
		 * neighbours so something is immediately visible. Does NOT itself
		 * scroll — `PhotoGridVirtual.vue` computes/animates the actual
		 * scroll offset once its own layout for the target bucket is known
		 * (layout is a rendering concern, kept out of the store).
		 */
		async loadV3(date?: string, photoId?: string): Promise<void> {
			this.isLoadingV3 = true;
			try {
				await this.ensureBucketsV3();

				let targetBucketId: string | undefined = date !== undefined && date !== "" ? date : undefined;
				if (photoId !== undefined && photoId !== "") {
					const resolved = await this.resolveBucketForPhotoV3(photoId);
					if (resolved !== null) {
						targetBucketId = resolved;
					}
				}

				if (targetBucketId !== undefined && this.boundariesV3.some((b) => b.bucketId === targetBucketId)) {
					await this.requestBucketWindow(this.neighborBucketIdsV3(targetBucketId));
					return;
				}

				// No usable deep link — load the first (most recent) window
				// so the grid isn't empty on a bare `/timeline` visit.
				const firstIds = this.boundariesV3.slice(0, 2).map((b) => b.bucketId);
				await this.requestBucketWindow(firstIds);
			} finally {
				this.isLoadingV3 = false;
			}
		},

		/**
		 * On-demand tier-3 (`details`) fetch — mirrors `AlbumState.ts`'s own
		 * `loadPhotoDetails()` exactly (same dedup-by-id, same in-place
		 * `mergePhotoDetail()` merge, same 300-id chunking), the Timeline
		 * counterpart `PhotoState.ts.load()` calls when `isTimelineSoaActive`
		 * is true.
		 */
		async loadPhotoDetailsV3(ids: string[]): Promise<void> {
			const photosState = usePhotosStore();

			const idsToFetch = [...new Set(ids)].filter((id) => !this.photoDetailsResolvedIdsV3[id] && photosState.photos.some((p) => p.id === id));
			if (idsToFetch.length === 0) {
				return;
			}

			const CHUNK_SIZE = 300;
			const chunks: string[][] = [];
			for (let i = 0; i < idsToFetch.length; i += CHUNK_SIZE) {
				chunks.push(idsToFetch.slice(i, i + CHUNK_SIZE));
			}

			try {
				const responses = await Promise.all(chunks.map((chunk) => PhotoChildrenV3Service.getDetails("timeline", { photoIds: chunk })));

				for (const response of responses) {
					const detail = response.data;
					for (let i = 0; i < detail.ids.length; i++) {
						const photo = photosState.photos.find((p) => p.id === detail.ids[i]);
						if (photo !== undefined) {
							mergePhotoDetail(photo, detail, i);
						}
					}
				}
				for (const id of idsToFetch) {
					this.photoDetailsResolvedIdsV3[id] = true;
				}
			} catch (error) {
				console.error(error);
			}
		},
	},
	getters: {
		/**
		 * Centralized dispatcher flag (mirrors `AlbumState.ts`'s own
		 * `isPhotoSoaActive`) — read by `Timeline.vue` (render/fetch
		 * dispatch), `PhotoGridVirtual.vue` (`source="timeline"` layout
		 * path), and `PhotoState.ts.load()` (on-demand `details` fetch
		 * gating).
		 */
		isTimelineSoaActive(): boolean {
			const lycheeStore = useLycheeStateStore();
			return lycheeStore.is_struct_of_array_enabled;
		},
		/** Mirrors `AlbumState.ts`'s own `photoBucketableV3` — Timeline has no per-album `is_photo_timeline_enabled` toggle to also gate on (grouping by date is the entire point of this view). */
		bucketableV3(state): boolean {
			return (state.bucketsV3?.bucketable ?? false) && state.boundariesV3.length > 1;
		},
	},
});
