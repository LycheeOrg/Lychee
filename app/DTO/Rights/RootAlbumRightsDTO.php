<?php

namespace App\DTO\Rights;

use App\Contracts\Models\AbstractAlbum;
use App\DTO\ArrayableDTO;
use App\Policies\AlbumPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Data Transfer Object (DTO) to transmit the rights of an user at the root level.
 */
class RootAlbumRightsDTO extends ArrayableDTO
{
	public function __construct(
		public bool $can_edit,     // needed because we want to allow user interaction like moving albums
		public bool $can_upload,
		public bool $can_import_from_server,
	) {
	}

	/**
	 * Create from current user.
	 *
	 * @return self
	 */
	public static function ofCurrentUser(): self
	{
		return new self(
			can_edit: Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, null]),
			can_upload: Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, null]),
			can_import_from_server: Gate::check(AlbumPolicy::CAN_IMPORT_FROM_SERVER, [AbstractAlbum::class]),
		);
	}
}