<?php

namespace App\DTO;

use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * This DTO provides the information whether some actions are available to the user.
 */
class PhotoRights extends ArrayableDTO
{
	public function __construct(
		public bool $can_edit,
		public bool $can_download,
		public bool $can_share_by_link,
		public bool $can_access_full_photo
	) {
	}

	/**
	 * Given a photo, returns the access rights associated to it.
	 *
	 * @param Photo $photo
	 *
	 * @return PhotoRights
	 */
	public static function ofPhoto(Photo $photo): PhotoRights
	{
		return new PhotoRights(
			can_edit: Gate::check(PhotoPolicy::CAN_EDIT, [Photo::class, $photo]),
			can_download: Gate::check(PhotoPolicy::CAN_DOWNLOAD, [Photo::class, $photo]),
			can_share_by_link: Gate::check(PhotoPolicy::CAN_SHARE_BY_LINK, [Photo::class, $photo]),
			can_access_full_photo: Gate::check(PhotoPolicy::CAN_ACCESS_FULL_PHOTO, [Photo::class, $photo]),
		);
	}
}