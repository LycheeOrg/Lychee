<?php

namespace App\DTO\Rights;

use App\DTO\ArrayableDTO;
use App\Models\Configs;
use App\Models\User;
use App\Policies\SettingsPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Data Transfer Object (DTO) to transmit the rights of a User at the root level.
 */
class SettingsRightsDTO extends ArrayableDTO
{
	public function __construct(
		public bool $can_edit,
		public bool $can_edit_own_settings,
		public bool $can_use_2fa,
		public bool $can_see_logs,
		public bool $can_clear_logs,
		public bool $can_see_diagnostics,
		public bool $can_update,
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
			can_edit: Gate::check(SettingsPolicy::CAN_EDIT, [Configs::class]),
			can_edit_own_settings: Gate::check(UserPolicy::CAN_EDIT_OWN_SETTINGS, [User::class]),
			can_use_2fa: Gate::check(SettingsPolicy::CAN_USE_2FA, [Configs::class]),
			can_see_logs: Gate::check(SettingsPolicy::CAN_SEE_LOGS, [Configs::class]),
			can_clear_logs: Gate::check(SettingsPolicy::CAN_CLEAR_LOGS, [Configs::class]),
			can_see_diagnostics: Gate::check(SettingsPolicy::CAN_SEE_DIAGNOSTICS, [Configs::class]),
			can_update: Gate::check(SettingsPolicy::CAN_UPDATE, Configs::class),
		);
	}

	/**
	 * @return self
	 */
	public static function ofTrue(): self
	{
		return new self(true, true, true, true, true, true, true);
	}
}