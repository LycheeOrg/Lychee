<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Settings;

use App\Http\Requests\BaseApiRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;

class SetJSSettingRequest extends BaseApiRequest
{
	public const ATTRIBUTE = 'js';
	protected string $js;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_EDIT, Configs::class);
	}

	public function getJs(): string
	{
		return $this->js;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [self::ATTRIBUTE => 'present|nullable|string'];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->js = $values[self::ATTRIBUTE] ?? '';
	}
}