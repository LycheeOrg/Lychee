<?php

namespace App\Http\Resources\Rights;

use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;

/**
 * Data Transfer Object (DTO) to transmit the rights of an user on the application settings.
 */
class SettingsRightsResource extends JsonResource
{
	public function __construct()
	{
		// Laravel applies a shortcut when this value === null but not when it is something else.
		parent::__construct('must_not_be_null');
	}

	/**
	 * Transform the resource into an array.
	 *
	 * @param \Illuminate\Http\Request $request
	 *
	 * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
	 */
	public function toArray($request)
	{
		return [
			'can_edit' => Gate::check(SettingsPolicy::CAN_EDIT, [Configs::class]),
			'can_see_logs' => Gate::check(SettingsPolicy::CAN_SEE_LOGS, [Configs::class]),
			'can_clear_logs' => Gate::check(SettingsPolicy::CAN_CLEAR_LOGS, [Configs::class]),
			'can_see_diagnostics' => Gate::check(SettingsPolicy::CAN_SEE_DIAGNOSTICS, [Configs::class]),
			'can_update' => Gate::check(SettingsPolicy::CAN_UPDATE, [Configs::class]),
		];
	}
}
