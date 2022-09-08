<?php

namespace App\DTO;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Data Transfer Object (DTO) to transmit the rights of a User.
 */
class UserRights extends DTO
{
	public bool $can_administrate;
	public bool $can_upload;
	public bool $can_edit_own_settings;

	public function __construct(
		bool $can_administrate,
		bool $can_upload,
		bool $can_edit_own_settings)
	{
		$this->can_administrate = $can_administrate;
		$this->can_upload = $can_upload;
		$this->can_edit_own_settings = $can_edit_own_settings;
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
			can_upload: Gate::check(UserPolicy::MAY_UPLOAD, User::class),
			can_edit_own_settings: Gate::check(UserPolicy::CAN_EDIT_OWN_SETTINGS, User::class)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'can_administrate' => $this->can_administrate,
			'can_upload' => $this->can_upload,
			'can_edit_own_settings' => $this->can_edit_own_settings,
		];
	}
}
