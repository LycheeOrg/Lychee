<?php

namespace App\Http\Requests\Settings;

use App\Contracts\Http\Requests\HasSeStatusBoolean;
use App\Http\Requests\AbstractEmptyRequest;
use App\Http\Requests\Traits\HasSeStatusBooleanTrait;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;

class GetAllConfigsRequest extends AbstractEmptyRequest implements HasSeStatusBoolean
{
	use HasSeStatusBooleanTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_EDIT, [Configs::class]);
	}
}