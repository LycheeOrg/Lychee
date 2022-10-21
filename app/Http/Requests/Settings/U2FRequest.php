<?php

namespace App\Http\Requests\Settings;

use App\Http\Requests\AbstractEmptyRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * @mixin Request
 */
class U2FRequest extends AbstractEmptyRequest
{
	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_USE_2FA, Configs::class);
	}
}
