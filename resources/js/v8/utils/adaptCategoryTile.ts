import { DEFAULT_ALBUM_CHILD_RIGHTS, type AdaptedAlbumTile } from "@/v8/utils/adaptAlbumChildTile";

/**
 * Adapts one row of a Feature 062 flat category listing (`GET /Albums/smart`,
 * `/tags`, `/persons`, `/pinned` — all share `AlbumCategoryResource`'s
 * shape: ids/titles/cover_ids/owner_ids) into the same `AdaptedAlbumTile`
 * shape the sub-album adapter produces (generalized
 * 2026-09-02 root-SoA addendum from its original smart-albums-only form) —
 * reused as-is rather than introducing a second adapted-tile type.
 *
 * Every field the lean category-list response doesn't carry gets a safe
 * default: `is_pinned`/`is_public`/`is_link_required`/`is_nsfw`/
 * `is_password_required` all `false` (none of these four category listings
 * expose them — a real, documented gap for `/tags`/`/persons`/`/pinned`
 * specifically, where a caller *could* have set e.g. `is_public` on one;
 * `/smart` alone is provably always `false`-correct here, see
 * AlbumProtectionPolicy::ofSmartAlbum() vs. real base-album policies),
 * `thumb` `null` (cover resolved via `cover_id` instead, mirroring
 * the sub-album adapter), `timeline`/`formatted_min_max` `null` (none of these four
 * listings carry `min_taken_at`/`max_taken_at`), `owner` `null` (a *name*,
 * not the `owner_ids[i]` these listings actually carry — resolving an id to
 * a display name would need a lookup this lean listing doesn't provide;
 * `contextMenu.ts`/tile rendering don't read this field anyway).
 *
 * `rights` defaults to `DEFAULT_ALBUM_CHILD_RIGHTS` (all-`false`, safe) —
 * correct for `/smart` (never editable) and for `/persons`/`/pinned` (no
 * rights endpoint exists for either — a caller loses
 * right-click edit/delete for a pinned/person tile at root when the flag is
 * on, an accepted, documented gap, not silently wrong). Callers with a real
 * rights source (`/tags` + `/tags/rights`) pass a precomputed `rights`
 * object instead.
 *
 * `kind` sets `is_tag_album`/`is_person_album` — read by `AlbumThumb.vue`/
 * `AlbumListItem.vue`'s existing tag/person badges (`scopeFlagsEnabled &&
 * album.is_tag_album`, same for person) once tag/person tiles are merged
 * into the root Smart Albums panel via `smartAlbums`'s existing
 * `baseSmartAlbums.concat(tagAlbums).concat(personAlbums)` getter — getting
 * this wrong would silently drop those badges for every v3-sourced tag/
 * person tile.
 */
export function adaptCategoryTile(
	i: number,
	data: App.Http.Resources.V3.AlbumCategoryResource,
	rights: App.Http.Resources.Rights.AlbumRightsResource = DEFAULT_ALBUM_CHILD_RIGHTS,
	kind: "smart" | "tag" | "person" | "pinned" = "smart",
): AdaptedAlbumTile {
	return {
		id: data.ids[i],
		title: data.titles[i],
		description: null,
		thumb: null,
		is_nsfw: false,
		is_pinned: kind === "pinned",
		is_public: false,
		is_link_required: false,
		is_password_required: false,
		is_tag_album: kind === "tag",
		is_person_album: kind === "person",
		has_subalbum: false,
		num_subalbums: 0,
		num_photos: 0,
		created_at: "",
		formatted_min_max: null,
		owner: null,
		rights: rights,
		timeline: null,
		cover_id: data.cover_ids[i],
	};
}

/**
 * Client-side rights combination for `/Albums/tags` + `/tags/rights`
 * (`AlbumCategoryRightsResource` — a *different*, leaner shape than
 * sub-album/root's `AlbumRightsResource`: no `can_delete_children`/
 * `can_move_children` at the parent level, `grants_delete` given directly
 * per row instead). `ownerId` comes from the *list* response's own
 * `owner_ids[i]` (`AlbumCategoryResource`, unlike `/smart` which has no
 * real owner) — `/tags` and `/tags/rights` are two independent queries, so
 * rows are matched by id, never by shared index. `can_move` is
 * unconditionally `false` — moving requires `is_model_album`
 * (`AlbumEdit.vue`'s existing `Move` gate), which a tag album never is.
 */
export function combineTagAlbumRights(
	ownerId: string,
	currentUserId: number | null | undefined,
	mayUpload: boolean | undefined,
	grantsEdit: boolean,
	grantsDownload: boolean,
	grantsDelete: boolean,
): App.Http.Resources.Rights.AlbumRightsResource {
	const isOwner = currentUserId !== undefined && currentUserId !== null && String(currentUserId) === ownerId;

	return {
		can_edit: (isOwner && (mayUpload ?? false)) || grantsEdit,
		can_download: isOwner || grantsDownload,
		can_delete: isOwner || grantsDelete,
		can_move: false,
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
