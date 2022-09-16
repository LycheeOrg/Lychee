<?php

namespace App\DTO;

use App\Contracts\AbstractAlbum;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * This DTO provides the information whether some actions are available to the user.
 */
class AlbumRights extends ArrayableDTO
{
	public function __construct(
		public bool $can_edit,
		public bool $can_share_with_users,
		public bool $can_download,
		public bool $can_upload,
		public bool $can_share_by_link
	) {
	}

	/**
	 * Given an album returns the access rights associated to it.
	 * TODO: Double check the different cases:
	 * - Tag albums
	 * - Smart albums
	 * - Normal albums.
	 *
	 * @param AbstractAlbum $abstractAlbum
	 *
	 * @return AlbumRights
	 */
	public static function ofAlbum(AbstractAlbum $abstractAlbum): AlbumRights
	{
		return new AlbumRights(
			can_edit: Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $abstractAlbum]),
			can_share_with_users: Gate::check(AlbumPolicy::CAN_SHARE_WITH_USERS, [AbstractAlbum::class, $abstractAlbum]),
			can_download: Gate::check(AlbumPolicy::CAN_DOWNLOAD, [AbstractAlbum::class, $abstractAlbum]),
			can_upload: Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, $abstractAlbum]),
			can_share_by_link: Gate::check(AlbumPolicy::CAN_SAHRE_BY_LINK, [AbstractAlbum::class, $abstractAlbum])
		);
	}
}