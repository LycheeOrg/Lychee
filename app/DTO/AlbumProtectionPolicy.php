<?php

namespace App\DTO;

use App\Contracts\AbstractAlbum;
use App\Models\Extensions\BaseAlbum;
use App\SmartAlbums\BaseSmartAlbum;

class AlbumProtectionPolicy extends ArrayableDTO
{
	public function __construct(
		public bool $is_public,
		public bool $is_link_required,
		public bool $is_nsfw,
		public bool $is_share_button_visible,
		public bool $grant_access_full_photo,
		public bool $grant_download,
		public bool $is_password_required = false,
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
				is_password_required: $abstractAlbum->password !== null && $abstractAlbum->password !== '',
				grant_access_full_photo: $abstractAlbum->grant_access_full_photo,
				grant_download: $abstractAlbum->grant_download,
			);
		}

		if ($abstractAlbum instanceof BaseSmartAlbum) {
			return new AlbumProtectionPolicy(
				is_public: $abstractAlbum->is_public,
				is_link_required: false,
				is_nsfw: false,
				is_share_button_visible: $abstractAlbum->is_share_button_visible,
				is_password_required: false,
				grant_access_full_photo: false,
				grant_download: false,
			);
		}

		return null;
	}
}
