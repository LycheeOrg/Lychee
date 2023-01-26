<?php

namespace App\DTO;

use App\Models\BaseAlbumImpl;
use App\Models\Configs;
use App\Models\Extensions\BaseAlbum;
use App\SmartAlbums\BaseSmartAlbum;

/**
 * This represents the Album Protection Policy.
 *
 * In other words it provides information on the security attributes of an album.
 * It MUST NOT be used in the front-end to determine whether an action is permitted or not (e.g. share link available).
 *
 * It is used in two places:
 * 1. When sent to the front-end:
 *   - provides the policy of the currently visited album
 *   - allows modification of the policy on non-smart albums
 * 2. When received from the front-end:
 *   - allows for an easy interface between the validated request {@link \App\Http\Requests\Album\SetAlbumProtectionPolicyRequest}
 *     and the applied action {@link \App\Actions\Album\SetProtectionPolicy}.
 */
class AlbumProtectionPolicy extends ArrayableDTO
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
	 * Given a {@link BaseAlbumImpl}, returns the Protection Policy associated to it.
	 *
	 * @param BaseAlbumImpl $baseAlbum
	 *
	 * @return AlbumProtectionPolicy
	 */
	public static function ofBaseAlbumImplementation(BaseAlbumImpl $baseAlbum): self
	{
		return new self(
			is_public: $baseAlbum->is_public,
			is_link_required: $baseAlbum->is_link_required,
			is_nsfw: $baseAlbum->is_nsfw,
			grants_full_photo_access: $baseAlbum->grants_full_photo_access,
			grants_download: $baseAlbum->grants_download,
			is_password_required: $baseAlbum->is_password_required,
		);
	}

	/**
	 * Given a {@link BaseAlbum}, returns the Protection Policy associated to it.
	 *
	 * @param BaseAlbum $baseAlbum
	 *
	 * @return AlbumProtectionPolicy
	 */
	public static function ofBaseAlbum(BaseAlbum $baseAlbum): self
	{
		return new self(
			is_public: $baseAlbum->is_public,
			is_link_required: $baseAlbum->is_link_required,
			is_nsfw: $baseAlbum->is_nsfw,
			grants_full_photo_access: $baseAlbum->grants_full_photo_access,
			grants_download: $baseAlbum->grants_download,
			is_password_required: $baseAlbum->is_password_required,
		);
	}

	/**
	 * Given a smart album, returns the Protection Policy associated to it.
	 *
	 * @param BaseSmartAlbum $baseSmartAlbum
	 *
	 * @return AlbumProtectionPolicy
	 */
	public static function ofSmartAlbum(BaseSmartAlbum $baseSmartAlbum): self
	{
		return new self(
			is_public: $baseSmartAlbum->is_public,
			is_link_required: false,
			is_nsfw: false,
			grants_full_photo_access: $baseSmartAlbum->grants_full_photo_access,
			grants_download: $baseSmartAlbum->grants_download,
			is_password_required: false,
		);
	}

	/**
	 * Create an {@link AlbumProtectionPolicy} for private defaults.
	 *
	 * @return AlbumProtectionPolicy
	 */
	public static function ofDefaultPrivate(): self
	{
		return new self(
			is_public: false,
			is_link_required: false,
			is_nsfw: false,
			grants_full_photo_access: Configs::getValueAsBool('grants_full_photo_access'),
			grants_download: Configs::getValueAsBool('grants_download'),
			is_password_required: false,
		);
	}

	/**
	 * Create an {@link AlbumProtectionPolicy} for public defaults.
	 *
	 * @return AlbumProtectionPolicy
	 */
	public static function ofDefaultPublic(): self
	{
		return new self(
			is_public: true,
			is_link_required: false,
			is_nsfw: false,
			grants_full_photo_access: Configs::getValueAsBool('grants_full_photo_access'),
			grants_download: Configs::getValueAsBool('grants_download'),
			is_password_required: false,
		);
	}
}
