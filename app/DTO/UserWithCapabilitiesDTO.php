<?php

namespace App\DTO;

use App\Models\User;

/**
 * Data Transfer Object (DTO) to transmit the capabilities of a user.
 *
 * This DTO is used by the admin part to manage users.
 *
 * If we directly return the user (e.g. for sharing), we only transmit the following attributes:
 * - id
 * - username
 * - email
 * - has_token
 *
 * This is because we do not send more than the front-end needs to know.
 */
class UserWithCapabilitiesDTO extends ArrayableDTO
{
	public function __construct(
		public int $id,
		public string $username,
		public bool $may_administrate,
		public bool $may_upload,
		public bool $may_edit_own_settings
	) {
	}

	/**
	 * Serialize User with the required attributes.
	 *
	 * @param User $user
	 *
	 * @return UserWithCapabilitiesDTO
	 */
	public static function ofUser(User $user): UserWithCapabilitiesDTO
	{
		return new UserWithCapabilitiesDTO(
			$user->id,
			$user->username,
			$user->may_administrate,
			$user->may_upload,
			$user->may_edit_own_settings
		);
	}
}