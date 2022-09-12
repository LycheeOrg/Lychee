<?php

namespace App\DTO;

use App\Contracts\AbstractAlbum;
use App\Models\User;
use App\Policies\AlbumPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Data Transfer Object (DTO) to transmit the rights of a User.
 */
class UserRights extends DTO
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
			can_administrate: Gate::check(UserPolicy::IS_ADMIN, User::class),
			can_upload_root: Gate::check(AlbumPolicy::CAN_UPLOAD, [AbstractAlbum::class, null]),
			can_edit_own_settings: Gate::check(UserPolicy::CAN_EDIT_OWN_SETTINGS, User::class)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return (array) $this;
	}
}
