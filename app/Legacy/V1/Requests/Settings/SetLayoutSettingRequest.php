<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Settings;

use App\Enum\PhotoLayoutType;
use Illuminate\Validation\Rules\Enum;

final class SetLayoutSettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'layout';

	public function rules(): array
	{
		return [
			self::ATTRIBUTE => ['required', new Enum(PhotoLayoutType::class)],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = self::ATTRIBUTE;
		$this->value = PhotoLayoutType::from($values[self::ATTRIBUTE]);
	}
}
