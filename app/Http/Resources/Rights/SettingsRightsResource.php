<?php

namespace App\Http\Resources\Rights;

use App\Http\Resources\JsonResource;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;

class SettingsRightsResource extends JsonResource
{
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
