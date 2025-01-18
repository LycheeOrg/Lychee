<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Settings;

use App\Enum\LicenseType;
use Illuminate\Validation\Rules\Enum;

final class SetDefaultLicenseSettingRequest extends AbstractSettingRequest
{
	public function rules(): array
	{
		return ['license' => ['required', new Enum(LicenseType::class)]];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = 'default_license';
		$this->value = LicenseType::from($values['license']);
	}
}
