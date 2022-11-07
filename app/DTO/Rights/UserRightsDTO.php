<?php

namespace App\DTO\Rights;

use App\DTO\ArrayableDTO;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Data Transfer Object (DTO) to transmit the rights of an user.
 */
class UserRightsDTO extends ArrayableDTO
{
	public function __construct(
		public bool $can_edit,
		public bool $can_use_2fa
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
			can_edit: Gate::check(UserPolicy::CAN_EDIT, [User::class]),
			can_use_2fa: Gate::check(UserPolicy::CAN_USE_2FA, [User::class]),
		);
	}

	/**
	 * @return self
	 */
	public static function ofUnregisteredAdmin(): self
	{
		return new self(true, true);
	}
}
