<?php

namespace App\Http\Requests\Diagnostics;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;

class DiagnosticsRequest extends AbstractEmptyRequest
{
	/**
	 * This Request is only available if the application is not installed yet.
	 * Thus, there's no authorization check here.
	 *
	 * @return bool
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_SEE_DIAGNOSTICS, Configs::class);
	}
}
