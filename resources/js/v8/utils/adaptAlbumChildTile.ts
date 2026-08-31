import { formatMinMaxDate } from "@/v8/utils/phpDateFormat";

/**
 * Safe default (FR-062-03): every right `false` until tier 3 resolves — a
 * right-click before the background fetch completes offers no actions
 * rather than incorrect ones.
 */
export const DEFAULT_ALBUM_CHILD_RIGHTS: App.Http.Resources.Rights.AlbumRightsResource = {
	can_edit: false,
	can_share: false,
	can_share_with_users: false,
	can_download: false,
	can_upload: false,
	can_move: false,
	can_delete: false,
	can_transfer: false,
	can_access_original: false,
	can_pasword_protect: false,
	can_import_from_server: false,
	can_make_purchasable: false,
	can_view_album_people: false,
	can_trigger_scan: false,
	can_assign_face: false,
	can_batch_face_ops: false,
};

/**
 * The actual shape of `albumsStore.albums` on the flag-on (v3) path:
 * `ThumbAlbumResource` plus `cover_id`, the one extra field tier 2 supplies
 * that the v2 type doesn't carry (FR-062-06, DO-062-01).
 */
export type AdaptedAlbumTile = App.Http.Resources.Models.ThumbAlbumResource & {
	cover_id: string | null;
};

/**
 * Client-side rights combination Feature 061 deliberately left
 * unimplemented (FR-062-04) — verified to match
 * `AlbumPolicy::canEdit`/`canDownload`/`canDelete` exactly
 * (`app/Policies/AlbumPolicy.php:255-269,184-207,281-303`).
 *
 * `rightsV3.owner_id` is "the parent `album_id`'s own `owner_id`"
 * (FR-061-20) — for a regular `Album` parent this equals every direct
 * child's own owner too, by Lychee's album-ownership-inheritance rule
 * (FR-061-20's invariant), so the owner-based shortcut below is sound. For
 * a `TagAlbum`/`PersonAlbum` parent (FR-061-25) it is the *tag/person's*
 * owner — unrelated to each dynamically-matched child's real owner — so
 * the shortcut is deliberately **not** applied there: `isRegularAlbumParent`
 * gates it off, leaving `can_edit`/`can_download` to `grants_edit[i]`/
 * `grants_download[i]` alone (still correct, since those are resolved
 * per-child server-side) and `can_delete`/`can_move` to tier 3's
 * unconditionally-`false` `can_delete_children`/`can_move_children` for
 * that case (FR-062-05) — applying the owner shortcut here would have been
 * a real over-granting bug (a caller who merely owns the browsed tag/person
 * grouping is not thereby the owner of every album that happens to match it).
 *
 * @param i        Index into tier 3's per-child arrays.
 * @param rightsV3 Tier 3 response (`AlbumChildrenRightsResource`).
 * @param isRegularAlbumParent Whether the browsed parent is a real `Album`
 *                 (`albumStore.modelAlbum !== undefined`) as opposed to a
 *                 `TagAlbum`/`PersonAlbum`.
 * @param currentUserId `useUserStore()`'s `user?.id` (`number | null`) —
 *                 `owner_id` is serialized as a `string`, so the comparison
 *                 coerces; `null`/`undefined` for a guest, never matches.
 * @param mayUpload `albumsStore.rootRights?.can_upload` — `UserResource`
 *                 itself never exposes the underlying `User::$may_upload`
 *                 column to the frontend, but `AlbumPolicy::canUpload($user,
 *                 null)` (the root-album case) resolves to exactly
 *                 `$user?->may_upload ?? false`, and `RootAlbumRightsResource`
 *                 already carries that as `can_upload` — reused here instead
 *                 of adding a new field anywhere.
 */
export function combineAlbumChildRights(
	i: number,
	rightsV3: App.Http.Resources.V3.AlbumChildrenRightsResource,
	isRegularAlbumParent: boolean,
	currentUserId: number | null | undefined,
	mayUpload: boolean | undefined,
): App.Http.Resources.Rights.AlbumRightsResource {
	const isOwner = isRegularAlbumParent && currentUserId !== undefined && currentUserId !== null && String(currentUserId) === rightsV3.owner_id;

	return {
		can_edit: (isOwner && (mayUpload ?? false)) || rightsV3.grants_edit[i],
		can_download: isOwner || rightsV3.grants_download[i],
		can_delete: isOwner || rightsV3.can_delete_children,
		can_move: isOwner || rightsV3.can_move_children,
		// Not offered by the right-click menu on a selection of albums
		// (confirmed against contextMenu.ts's actual field reads) — Feature
		// 061 deliberately excludes these signals from tier 3 (Non-Goals).
		can_share: false,
		can_share_with_users: false,
		can_transfer: false,
		can_upload: false,
		can_access_original: false,
		can_pasword_protect: false,
		can_import_from_server: false,
		can_make_purchasable: false,
		can_view_album_people: false,
		can_trigger_scan: false,
		can_assign_face: false,
		can_batch_face_ops: false,
	};
}

/**
 * Adapts one tier-2 child (by index) into an `AdaptedAlbumTile` (FR-062-06).
 * `thumb`/`timeline` are left `null` — resolved independently by
 * `AlbumThumbVirtual.vue`/`AlbumListItemVirtual.vue` (FR-062-15/17) and by
 * bucket-driven sectioning (FR-062-08) respectively, not read from this
 * object in the flag-on path. `is_pinned`/`is_public`/`is_link_required` are
 * mapped straight through from tier 2 (FR-061-27) — no client-side
 * computation. `formatted_min_max` is computed client-side (FR-062-16).
 *
 * @param i          Index into tier 2's per-child arrays.
 * @param childrenV3 Tier 2 response (`AlbumChildrenDataResource`).
 * @param rights     This child's already-combined rights (or the safe
 *                   all-`false` default before tier 3 resolves, FR-062-03).
 * @param dateFormatAlbumThumb `date_format_album_thumb` config value.
 * @param thumbMinMaxOrder     `thumb_min_max_order` config value.
 */
export function adaptAlbumChildTile(
	i: number,
	childrenV3: App.Http.Resources.V3.AlbumChildrenDataResource,
	rights: App.Http.Resources.Rights.AlbumRightsResource,
	dateFormatAlbumThumb: string,
	thumbMinMaxOrder: App.Enum.DateOrderingType,
): AdaptedAlbumTile {
	return {
		id: childrenV3.ids[i],
		title: childrenV3.titles[i],
		description: childrenV3.descriptions[i],
		thumb: null,
		is_nsfw: childrenV3.is_nsfws[i],
		is_pinned: childrenV3.is_pinneds[i],
		is_public: childrenV3.is_publics[i],
		is_link_required: childrenV3.is_link_requireds[i],
		is_password_required: childrenV3.is_password_requireds[i],
		// Direct children of a real Album are never themselves a Tag/Person
		// album — only the browsed parent can be one (FR-061-24).
		is_tag_album: false,
		is_person_album: false,
		has_subalbum: childrenV3.has_subalbums[i],
		num_subalbums: childrenV3.num_subalbums[i],
		num_photos: childrenV3.num_photos[i],
		created_at: childrenV3.created_ats[i],
		formatted_min_max: formatMinMaxDate(childrenV3.min_taken_ats[i], childrenV3.max_taken_ats[i], dateFormatAlbumThumb, thumbMinMaxOrder),
		owner: null,
		rights: rights,
		timeline: null,
		cover_id: childrenV3.cover_ids[i],
	};
}
