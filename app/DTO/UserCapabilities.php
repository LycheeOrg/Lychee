<?php

namespace App\DTO;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Data Transfer Object (DTO) to transmit the capabilities of a User.
 */
class UserCapabilities extends DTO
{
	public bool $may_administrate;
	public bool $may_upload;
	public bool $may_edit_own_settings;

	public function __construct(
		bool $may_administrate,
		bool $may_upload,
		bool $may_edit_own_settings)
	{
		$this->may_administrate = $may_administrate;
		$this->may_upload = $may_upload;
		$this->may_edit_own_settings = $may_edit_own_settings;
	}

	/**
	 * Create from current user.
	 *
	 * @return UserCapabilities
	 */
	public static function ofCurrentUser(): UserCapabilities
	{
		return new UserCapabilities(
			Gate::check(UserPolicy::IS_ADMIN, User::class),
			Gate::check(UserPolicy::MAY_UPLOAD, User::class),
			Gate::check(UserPolicy::MAY_EDIT_OWN_SETTINGS, User::class)
		);
	}

	/**
	 * {@inheritDoc}
	 */
	public function toArray(): array
	{
		return [
			'may_administrate' => $this->may_administrate,
			'may_upload' => $this->may_upload,
			'may_edit_own_settings' => $this->may_edit_own_settings,
		];
	}
}
