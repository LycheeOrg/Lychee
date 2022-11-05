<?php

namespace App\DTO;

use App\Models\BaseAlbumImpl;
use App\SmartAlbums\BaseSmartAlbum;

/**
 * This represents the Album Protection policies.
 *
 * In other words it provides information on the security attributes of an album.
 * It MUST NOT be used in the front-end to determine whether an action is permitted or not (e.g. share link available).
 *
 * It is used in two places:
 * 1. When sent to the front-end:
 *   - provides the policies of the currently visited album
 *   - allows modification of the policies on non-smart albums
 * 2. When received from the front-end:
 *   - allows for an easy interface between the validated request {@link \App\Http\Requests\Album\SetAlbumProtectionPoliciesRequest}
 *     and the applied action {@link \App\Actions\Album\SetProtectionPolicies}.
 */
class AlbumProtectionPolicies extends ArrayableDTO
{
	public function __construct(
		public bool $is_public,
		public bool $is_link_required,
		public bool $is_nsfw,
		public bool $grants_full_photo_access,
		public bool $grants_download,
		public bool $is_password_required = false, // Only used when sending info to the front-end
	) {
	}

	/**
	 * Given a BaseAlbumImplementation, returns the Protection Policies associated to it.
	 *
	 * @param BaseAlbumImpl $baseAlbum
	 *
	 * @return AlbumProtectionPolicies
	 */
	public static function ofBaseAlbumImplementation(BaseAlbumImpl $baseAlbum): AlbumProtectionPolicies
	{
		return new AlbumProtectionPolicies(
			is_public: $baseAlbum->is_public,
			is_link_required: $baseAlbum->is_link_required,
			is_nsfw: $baseAlbum->is_nsfw,
			grants_full_photo_access: $baseAlbum->grants_full_photo_access,
			grants_download: $baseAlbum->grants_download,
			is_password_required: $baseAlbum->password !== null && $baseAlbum->password !== '',
		);
	}

	/**
	 * Given a smart album, returns the Protection Policies associated to it.
	 *
	 * @param BaseSmartAlbum $baseSmartAlbum
	 *
	 * @return AlbumProtectionPolicies
	 */
	public static function ofSmartAlbum(BaseSmartAlbum $baseSmartAlbum): AlbumProtectionPolicies
	{
		return new AlbumProtectionPolicies(
			is_public: $baseSmartAlbum->is_public,
			is_link_required: false,
			is_nsfw: false,
			grants_full_photo_access: $baseSmartAlbum->grants_full_photo_access,
			grants_download: $baseSmartAlbum->grants_download,
			is_password_required: false,
		);
	}
}
