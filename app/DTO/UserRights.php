<?php

namespace App\DTO;

use App\Contracts\AbstractAlbum;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\BasePolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Data Transfer Object (DTO) to transmit the rights of a User.
 *
 * TODO: REMOVE ME
 */
class UserRights extends ArrayableDTO
{
	public function __construct(
		public bool $can_administrate,
		public bool $can_upload_root,
		public bool $can_edit_own_settings)
	{
	}

	/**
	 * Create from current user.
	 *
	 * @return UserRights
	 */
	public static function ofCurrentUser(): UserRights
	{
		return new UserRights(
			can_administrate: Gate::check(BasePolicy::IS_ADMIN, User::class),
			can_upload_root: Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, null]),
			can_edit_own_settings: Gate::check(UserPolicy::CAN_EDIT_OWN_SETTINGS, User::class)
		);
	}
}
