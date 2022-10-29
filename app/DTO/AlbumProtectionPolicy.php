<?php

namespace App\DTO;

use App\Models\BaseAlbumImpl;
use App\Models\Extensions\BaseAlbum;
use App\SmartAlbums\BaseSmartAlbum;

/**
 * This represents the Album Protection policy.
 *
 * In other words it provides information on the security attributes of an album.
 * It MUST NOT be used in the front-end to determine whether an action is permitted or not (e.g. share link available).
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
		public bool $grants_access_full_photo,
		public bool $grants_download,
		public bool $is_password_required = false, // Only used when sending info to the front-end
	) {
	}

	/**
	 * Given a BaseAlbum, returns the Protection Policy associated to it.
	 *
	 * @param BaseAlbum|BaseAlbumImpl $baseAlbum
	 *
	 * @return AlbumProtectionPolicy
	 */
	public static function ofBaseAlbum(BaseAlbum|BaseAlbumImpl $baseAlbum): AlbumProtectionPolicy
	{
		return new AlbumProtectionPolicy(
			is_public: $baseAlbum->is_public,
			is_link_required: $baseAlbum->is_link_required,
			is_nsfw: $baseAlbum->is_nsfw,
			grants_access_full_photo: $baseAlbum->grants_access_full_photo,
			grants_download: $baseAlbum->grants_download,
			is_password_required: $baseAlbum->password !== null && $baseAlbum->password !== '',
		);
	}

	/**
	 * Given a smart album, returns the Protection Policy associated to it.
	 *
	 * @param BaseSmartAlbum $baseSmartAlbum
	 *
	 * @return AlbumProtectionPolicy
	 */
	public static function ofSmartAlbum(BaseSmartAlbum $baseSmartAlbum): AlbumProtectionPolicy
	{
		return new AlbumProtectionPolicy(
			is_public: $baseSmartAlbum->is_public,
			is_link_required: false,
			is_nsfw: false,
			grants_access_full_photo: $baseSmartAlbum->grants_access_full_photo,
			grants_download: $baseSmartAlbum->grants_download,
			is_password_required: false,
		);
	}
}
