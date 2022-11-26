<?php

namespace App\DTO\Rights;

use App\DTO\ArrayableDTO;
use App\Models\Configs;
use App\Models\User;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;

/**
 * Data Transfer Object (DTO) to transmit the rights of an user on the application settings.
 */
class SettingsRightsDTO extends ArrayableDTO
{
	public function __construct(
		public bool $can_edit,
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
			can_see_logs: Gate::check(SettingsPolicy::CAN_SEE_LOGS, [Configs::class]),
			can_clear_logs: Gate::check(SettingsPolicy::CAN_CLEAR_LOGS, [Configs::class]),
			can_see_diagnostics: Gate::check(SettingsPolicy::CAN_SEE_DIAGNOSTICS, [Configs::class]),
			can_update: Gate::check(SettingsPolicy::CAN_UPDATE, [Configs::class]),
		);
	}
}