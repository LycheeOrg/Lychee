// import { ALL } from "@/config/constants";
// import AlbumService from "@/services/album-service";
import { defineStore } from "pinia";
// import { useTogglablesStateStore } from "./ModalsState";
import { type SplitData, useSplitter } from "@/composables/album/splitter";
import AlbumService from "@/services/album-service";
import AlbumCategoryV3Service from "@/services/album-category-v3-service";
import { adaptCategoryTile, combineTagAlbumRights } from "@/v8/utils/adaptCategoryTile";
import { adaptAlbumChildTile, combineAlbumChildRights, DEFAULT_ALBUM_CHILD_RIGHTS, type AdaptedAlbumTile } from "@/v8/utils/adaptAlbumChildTile";
import { computeBucketBoundaries, type AlbumBucketBoundary } from "@/v8/utils/albumBucketBoundaries";
import { useTogglablesStateStore } from "./ModalsState";
import { useUserStore } from "./UserState";
import { useLycheeStateStore } from "./LycheeState";
import { Router } from "vue-router";
import InitService from "@/services/init-service";

const { spliter } = useSplitter();

export type { SplitData };
export type AlbumsStore = ReturnType<typeof useAlbumsStore>;

export const useAlbumsStore = defineStore("albums-store", {
	state: () => ({
		isLoading: false as boolean,
		baseSmartAlbums: [] as App.Http.Resources.Models.ThumbAlbumResource[],
		tagAlbums: [] as App.Http.Resources.Models.ThumbAlbumResource[],
		personAlbums: [] as App.Http.Resources.Models.ThumbAlbumResource[],
		albums: [] as App.Http.Resources.Models.ThumbAlbumResource[],
		pinnedAlbums: [] as App.Http.Resources.Models.ThumbAlbumResource[],
		sharedAlbums: [] as SplitData<App.Http.Resources.Models.ThumbAlbumResource>[],
		rootConfig: undefined as App.Http.Resources.GalleryConfigs.RootConfig | undefined,
		rootRights: undefined as App.Http.Resources.Rights.RootAlbumRightsResource | undefined,
		// 2026-09-02 root-SoA addendum (full scope: "the point of the
		// extension is to support Struct-of-Array on the root gallery
		// page") — own/shared root albums additionally carry bucket
		// tier1 state (mirroring AlbumState.ts's bucketsV3/boundariesV3 for
		// sub-album children, since AlbumRootController literally reuses
		// the same AlbumBucketResource/AlbumDataResource/
		// AlbumRightsResource shapes). `albums` itself doubles as
		// the "own" scope's adapted-tile array on the flag-on path (same
		// field, same shape contract the flag-off path already populates,
		// mirroring how `baseSmartAlbums` already does this); `shared`
		// scope gets its own flat array (`sharedAlbumsV3`) since the
		// flag-off `sharedAlbums` field's grouped-`SplitData` shape is
		// specific to the old client-side owner-grouping this addendum's
		// bucketed grid replaces.
		ownBucketsV3: undefined as App.Http.Resources.V3.AlbumBucketResource | undefined,
		ownBoundariesV3: null as AlbumBucketBoundary[] | null,
		sharedAlbumsV3: [] as AdaptedAlbumTile[],
		sharedBucketsV3: undefined as App.Http.Resources.V3.AlbumBucketResource | undefined,
		sharedBoundariesV3: null as AlbumBucketBoundary[] | null,
	}),
	getters: {
		smartAlbums(state): App.Http.Resources.Models.ThumbAlbumResource[] {
			return state.baseSmartAlbums.concat(state.tagAlbums).concat(state.personAlbums);
		},
		// We use state here because we want the RETURN type inference
		selectableAlbums(state): App.Http.Resources.Models.ThumbAlbumResource[] {
			// Note that selectableAlbums has to reflect the same order as pinned/unpinned albums.
			// Deduplicate by id: when deduplicate_pinned_albums is disabled, pinned albums appear
			// in both pinnedAlbums and albums, so we only keep the first occurrence.
			const seen = new Set<string>();
			return state.pinnedAlbums
				.concat(state.albums)
				.concat(state.sharedAlbums.map((album) => album.data).flat())
				.concat(state.sharedAlbumsV3)
				.filter((a) => {
					if (seen.has(a.id)) return false;
					seen.add(a.id);
					return true;
				});
		},
		// We use `this` in this one because we want the type inference of selectableAlbums
		hasHidden(): boolean {
			return this.selectableAlbums.filter((album) => album.is_nsfw).length > 0;
		},
		/** Mirrors `AlbumState.ts`'s `bucketableV3` getter, own-root scope. */
		ownBucketableV3(state): boolean {
			return (state.ownBucketsV3?.bucketable ?? false) && state.ownBoundariesV3 !== null && state.ownBoundariesV3.length > 1;
		},
		/** Mirrors `AlbumState.ts`'s `bucketableV3` getter, shared-root scope. */
		sharedBucketableV3(state): boolean {
			return (state.sharedBucketsV3?.bucketable ?? false) && state.sharedBoundariesV3 !== null && state.sharedBoundariesV3.length > 1;
		},
	},
	actions: {
		reset() {
			this.isLoading = false;
			this.baseSmartAlbums = [];
			this.tagAlbums = [];
			this.personAlbums = [];
			this.albums = [];
			this.pinnedAlbums = [];
			this.sharedAlbums = [];
			this.rootConfig = undefined;
			this.rootRights = undefined;
			this.ownBucketsV3 = undefined;
			this.ownBoundariesV3 = null;
			this.sharedAlbumsV3 = [];
			this.sharedBucketsV3 = undefined;
			this.sharedBoundariesV3 = null;
		},
		loadRootRights(): Promise<void> {
			return InitService.fetchGlobalRights().then((data) => {
				this.rootRights = data.data.root_album;
			});
		},
		/**
		 * Fetches `GET /Albums/smart` and adapts each row into
		 * `this.baseSmartAlbums` (2026-09-02 addendum) — the flag-on
		 * replacement for `load()`'s v2 `data.data.smart_albums` read. `tagAlbums`/
		 * `personAlbums` are unaffected, still v2-sourced regardless of the flag
		 * — the `smartAlbums` getter concatenates all three either way.
		 */
		loadSmartAlbumsV3(): Promise<void> {
			return AlbumCategoryV3Service.getSmart()
				.then((data) => {
					this.baseSmartAlbums = data.data.ids.map((_, i) => adaptCategoryTile(i, data.data, DEFAULT_ALBUM_CHILD_RIGHTS, "smart"));
				})
				.catch((error) => {
					console.error(error);
				});
		},
		/**
		 * Fetches `GET /Albums/tags` + `/Albums/tags/rights` and combines
		 * them into `this.tagAlbums`,
		 * merged into the root Smart Albums panel via the existing
		 * `smartAlbums` getter (unchanged — tag albums were already merged
		 * there in v2). Unlike smart albums, tag albums have a real rights
		 * source and a real owner, so rights are properly combined
		 * (`combineTagAlbumRights()`) rather than defaulted.
		 */
		loadTagAlbumsV3(): Promise<void> {
			const userStore = useUserStore();

			return Promise.all([AlbumCategoryV3Service.getTags(), AlbumCategoryV3Service.getTagsRights()])
				.then(([listResponse, rightsResponse]) => {
					const list = listResponse.data;
					const rights = rightsResponse.data;
					const rightsIndexById = new Map(rights.ids.map((id, i) => [id, i]));

					this.tagAlbums = list.ids.map((id, i) => {
						const ri = rightsIndexById.get(id);
						const combined =
							ri !== undefined
								? combineTagAlbumRights(
										list.owner_ids[i],
										userStore.user?.id,
										this.rootRights?.can_upload,
										rights.grants_edit[ri],
										rights.grants_download[ri],
										rights.grants_delete[ri],
									)
								: DEFAULT_ALBUM_CHILD_RIGHTS;

						return adaptCategoryTile(i, list, combined, "tag");
					});
				})
				.catch((error) => {
					console.error(error);
				});
		},
		/**
		 * Fetches `GET /Albums/persons` for both `own`/`shared` scope
		 * and merges them into `this.personAlbums` —
		 * v2's `person_albums` field is likewise an unpartitioned own+shared
		 * union, so both scopes are fetched and concatenated client-side (no
		 * single "all" route exists at the API level, `GetScopedAlbumsRequest`
		 * requires an explicit scope for an authenticated caller). A guest
		 * only ever gets `shared` (`scope=own` is 422 for a guest). No rights
		 * endpoint exists for persons — rights default to
		 * `DEFAULT_ALBUM_CHILD_RIGHTS`, an accepted, documented gap (loses
		 * right-click edit/delete for a person-album tile at root).
		 */
		loadPersonAlbumsV3(): Promise<void> {
			const userStore = useUserStore();
			const requests = userStore.isLoggedIn
				? [AlbumCategoryV3Service.getPersons("own"), AlbumCategoryV3Service.getPersons("shared")]
				: [AlbumCategoryV3Service.getPersons("shared")];

			return Promise.all(requests)
				.then((responses) => {
					this.personAlbums = responses.flatMap((response) =>
						response.data.ids.map((_, i) => adaptCategoryTile(i, response.data, DEFAULT_ALBUM_CHILD_RIGHTS, "person")),
					);
				})
				.catch((error) => {
					console.error(error);
				});
		},
		/**
		 * Fetches `GET /Albums/pinned` for both `own`/`shared` scope,
		 * merged the same way `loadPersonAlbumsV3()`
		 * merges persons — v2's `pinned_albums` is likewise an unpartitioned
		 * union. No rights endpoint exists for pinned either —
		 * same accepted rights gap as persons.
		 */
		loadPinnedAlbumsV3(): Promise<void> {
			const userStore = useUserStore();
			const requests = userStore.isLoggedIn
				? [AlbumCategoryV3Service.getPinned("own"), AlbumCategoryV3Service.getPinned("shared")]
				: [AlbumCategoryV3Service.getPinned("shared")];

			return Promise.all(requests)
				.then((responses) => {
					this.pinnedAlbums = responses.flatMap((response) =>
						response.data.ids.map((_, i) => adaptCategoryTile(i, response.data, DEFAULT_ALBUM_CHILD_RIGHTS, "pinned")),
					);
				})
				.catch((error) => {
					console.error(error);
				});
		},
		/**
		 * Fetches tier 1 (`GET /Albums/root/buckets?scope=`) + tier 2
		 * (`GET /Albums/root?scope=`) together for the given scope and adapts
		 * each row — the root-scope sibling of `AlbumState.ts`'s
		 * `loadAlbumsV3()`, reusing the exact same `computeBucketBoundaries()`/
		 * `adaptAlbumChildTile()` pure functions (`AlbumRootController`
		 * literally returns the same `AlbumBucketResource`/
		 * `AlbumDataResource` shapes sub-album children already use).
		 * `own` scope's adapted tiles are written into the existing `albums`
		 * field (same field/shape the flag-off path already populates,
		 * mirroring `baseSmartAlbums`); `shared` scope gets the new flat
		 * `sharedAlbumsV3` field instead of the flag-off `sharedAlbums`
		 * field's grouped `SplitData` shape (a genuinely different rendering
		 * model — bucketed-by-owner virtualized grid vs. one panel per owner).
		 */
		loadRootAlbumsV3(scope: App.Enum.AlbumListingScope): Promise<void> {
			return Promise.all([AlbumCategoryV3Service.getRootBuckets(scope), AlbumCategoryV3Service.getRootChildren(scope)])
				.then(([bucketsResponse, childrenResponse]) => {
					const buckets = bucketsResponse.data;
					const children = childrenResponse.data;
					const boundaries = computeBucketBoundaries(buckets, children.ids.length);
					const dateFormat = this.rootConfig?.date_format_album_thumb ?? "M Y";
					const dateOrder = this.rootConfig?.thumb_min_max_order ?? "younger_older";

					const tiles = children.ids.map((_, i) => adaptAlbumChildTile(i, children, DEFAULT_ALBUM_CHILD_RIGHTS, dateFormat, dateOrder));

					if (scope === "own") {
						this.ownBucketsV3 = buckets;
						this.ownBoundariesV3 = boundaries;
						this.albums = tiles;
					} else {
						this.sharedBucketsV3 = buckets;
						this.sharedBoundariesV3 = boundaries;
						this.sharedAlbumsV3 = tiles;
					}
				})
				.catch((error) => {
					console.error(error);
				});
		},
		/**
		 * Background-fetches tier 3 (`GET /Albums/root/rights?scope=`) and
		 * reactively merges combined rights into the already-adapted tiles for
		 * that scope (mirrors `AlbumState.ts`'s `loadAlbumsV3Rights()`).
		 * `isOwner` is simply `scope === 'own'` — root's `own`/`shared` query
		 * already partitions by ownership at the SQL level
		 * (`AlbumRootController::baseQuery()`), so every row in an
		 * `own`-scope response is unconditionally the caller's own, and
		 * root's `AlbumRightsResource.owner_id` is `Optional`/omitted
		 * entirely — there is no per-row owner id to compare
		 * against here, unlike sub-album children.
		 */
		loadRootAlbumsV3Rights(scope: App.Enum.AlbumListingScope): Promise<void> {
			const isOwner = scope === "own";

			return AlbumCategoryV3Service.getRootRights(scope)
				.then((response) => {
					const rightsV3 = response.data;
					const indexById = new Map(rightsV3.ids.map((id, i) => [id, i]));

					if (scope === "own") {
						this.albums = this.albums.map((tile) => {
							const i = indexById.get(tile.id);
							if (i === undefined) {
								return tile;
							}
							return { ...tile, rights: combineAlbumChildRights(i, rightsV3, isOwner, this.rootRights?.can_upload) };
						});
					} else {
						this.sharedAlbumsV3 = this.sharedAlbumsV3.map((tile) => {
							const i = indexById.get(tile.id);
							if (i === undefined) {
								return tile;
							}
							return { ...tile, rights: combineAlbumChildRights(i, rightsV3, isOwner, this.rootRights?.can_upload) };
						});
					}
				})
				.catch((error) => {
					console.error(error);
				});
		},
		load(router: Router): Promise<void> {
			const togglableState = useTogglablesStateStore();
			const userStore = useUserStore();
			const lycheeStore = useLycheeStateStore();

			if (this.isLoading) {
				return Promise.resolve();
			}

			this.isLoading = true;
			return AlbumService.getAll()
				.then((data) => {
					// `config`/`rights` have no v3 replacement source (v2's
					// Top::get() stays byte-identical, and none of its
					// five new endpoints carry a config/rights-for-the-page-itself
					// field) — this v2 call stays in the loop even when the flag
					// is on, purely for these two fields.
					this.rootConfig = data.data.config;
					this.rootRights = data.data.rights;

					const listsLoaded = lycheeStore.is_struct_of_array_enabled
						? Promise.all([
								this.loadSmartAlbumsV3(),
								this.loadTagAlbumsV3(),
								this.loadPersonAlbumsV3(),
								this.loadPinnedAlbumsV3(),
								...(userStore.isLoggedIn ? [this.loadRootAlbumsV3("own").then(() => void this.loadRootAlbumsV3Rights("own"))] : []),
								this.loadRootAlbumsV3("shared").then(() => void this.loadRootAlbumsV3Rights("shared")),
							]).then(() => {})
						: Promise.resolve().then(() => {
								this.baseSmartAlbums = data.data.smart_albums ?? [];
								this.tagAlbums = data.data.tag_albums;
								this.personAlbums = data.data.person_albums ?? [];
								this.albums = data.data.albums;
								this.pinnedAlbums = data.data.pinned_albums;
								this.sharedAlbums = spliter(
									data.data.shared_albums ?? [],
									(d) => d.owner ?? "(unknown)", // mapper
									(d) => d.owner ?? "(unknown)", // formatter
									this.albums.length,
								);
							});

					return listsLoaded.then(() => {
						// If we are not logged in and there are no albums, we redirect to the login page.
						if (
							(userStore.user?.id === undefined || userStore.user?.id === null) &&
							this.albums.length === 0 &&
							this.smartAlbums.length === 0 &&
							this.sharedAlbums.length === 0 &&
							this.sharedAlbumsV3.length === 0
						) {
							router.push({ name: "login" });
						}
					});
				})
				.catch((error) => {
					// We are required to login :)
					// We use the modal instead of the login page to avoid the redirect back.
					// Once logged in, we just refresh the page.
					if (error.response && error.response.status === 401) {
						togglableState.is_login_open = true;
						console.error("require login");
					} else {
						console.error(error);
					}
				})
				.finally(() => {
					this.isLoading = false;
				});
		},
	},
});
