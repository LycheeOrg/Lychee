<?php

namespace App\Http\Resources\Rights;

use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript()]
class SettingsRightsResource extends Data
{
	public bool $can_edit;
	public bool $can_see_logs;
	public bool $can_clear_logs;
	public bool $can_see_diagnostics;
	public bool $can_update;

	public function __construct()
	{
		$this->can_edit = Gate::check(SettingsPolicy::CAN_EDIT, [Configs::class]);
		$this->can_see_logs = Gate::check(SettingsPolicy::CAN_SEE_LOGS, [Configs::class]);
		$this->can_clear_logs = Gate::check(SettingsPolicy::CAN_CLEAR_LOGS, [Configs::class]);
		$this->can_see_diagnostics = Gate::check(SettingsPolicy::CAN_SEE_DIAGNOSTICS, [Configs::class]);
		$this->can_update = Gate::check(SettingsPolicy::CAN_UPDATE, [Configs::class]);
	}
}
