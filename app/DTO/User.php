<?php

namespace App\DTO;

use App\Models\User as UserModel;

/**
 * Data Transfer Object (DTO) to transmit the attributes of a User.
 */
class User extends DTO
{
	public function __construct(
		private int $id,
		private string $username,
		private bool $may_administrate,
		private bool $may_upload,
		private bool $may_edit_own_settings
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

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return (array) $this;
	}
}