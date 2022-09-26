<?php

namespace App\DTO;

use App\Contracts\AbstractAlbum;
use App\Models\Extensions\BaseAlbum;
use App\SmartAlbums\BaseSmartAlbum;

/**
 * This represents the Album Protection policy.
 *
 * In other words it provides information on the security attributes of an album.
 * It MUST not be used in the front-end to determine whether an action is permitted or not (e.g. share link available).
 *
 * It is used in two places:
 * 1. When sent to the front-end:
 *   - provides an overview of the policy of the current visited album
 *   - allows modification of the policy on non-smart albums
 * 2. When gotten from the front-end:
 *   - to allow easy interface between the validated request {@link \App\Http\Requests\Album\SetAlbumProtectionPolicyRequest}
 *     and the applied action {@link \App\Actions\Album\SetProtectionPolicy}.
 */
class AlbumProtectionPolicy extends ArrayableDTO
{
	public function __construct(
		public bool $is_public,
		public bool $is_link_required,
		public bool $is_nsfw,
		public bool $is_share_button_visible,
		public bool $grants_access_full_photo,
		public bool $grants_download,
		public bool $is_password_required = false, // Only used when sending info to the front-end
	) {
	}

	/**
	 * Given an album returns the Protection Policy associated to it.
	 * TODO: Double check the different cases:
	 * - Tag albums
	 * - Smart albums
	 * - Normal albums.
	 *
	 * @param AbstractAlbum $abstractAlbum
	 *
	 * @return AlbumProtectionPolicy|null
	 */
	public static function ofAlbum(AbstractAlbum $abstractAlbum): AlbumProtectionPolicy|null
	{
		if ($abstractAlbum instanceof BaseAlbum) {
			return new AlbumProtectionPolicy(
				is_public: $abstractAlbum->is_public,
				is_link_required: $abstractAlbum->is_link_required,
				is_nsfw: $abstractAlbum->is_nsfw,
				is_share_button_visible: $abstractAlbum->is_share_button_visible,
				grants_access_full_photo: $abstractAlbum->grant_access_full_photo,
				grants_download: $abstractAlbum->grant_download,
				is_password_required: $abstractAlbum->password !== null && $abstractAlbum->password !== '',
			);
		}

		if ($abstractAlbum instanceof BaseSmartAlbum) {
			return new AlbumProtectionPolicy(
				is_public: $abstractAlbum->is_public, // TODO: FIX ME
				is_link_required: false, // TODO: FIX ME
				is_nsfw: false,
				is_share_button_visible: $abstractAlbum->is_share_button_visible, // TODO: FIX ME
				grants_access_full_photo: false, // TODO: FIX ME
				grants_download: false,
				is_password_required: false,
			);
		}

		return null;
	}
}
