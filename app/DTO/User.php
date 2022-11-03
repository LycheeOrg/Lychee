<?php

namespace App\DTO;

use App\Models\User as UserModel;

/**
 * Data Transfer Object (DTO) to transmit the capabilities of a User.
 *
 * This is used in the Admin part to manage users.
 *
 * If we return directly the User (e.g. for sharing), we are given only the following attributes:
 * - id
 * - username
 * - email
 * - has_token
 *
 * This is because we do not send more than the front-end needs to know.
 */
class User extends ArrayableDTO
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
	 * @param UserModel $user
	 *
	 * @return User
	 */
	public static function ofUser(UserModel $user): User
	{
		return new User(
			$user->id,
			$user->username,
			$user->may_administrate,
			$user->may_upload,
			$user->may_edit_own_settings
		);
	}
}