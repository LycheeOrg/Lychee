<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Settings;

use App\Http\Requests\BaseApiRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * @mixin Request
 */
abstract class AbstractSettingRequest extends BaseApiRequest
{
	protected string|int|bool|\BackedEnum $value;

	protected string $name;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_EDIT, Configs::class);
	}

	public function getSettingName(): string
	{
		return $this->name;
	}

	public function getSettingValue(): string|int|bool|\BackedEnum
	{
		return $this->value;
	}
}
