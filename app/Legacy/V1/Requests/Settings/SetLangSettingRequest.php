<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Settings;

use Illuminate\Validation\Rule;

final class SetLangSettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'lang';

	public function rules(): array
	{
		return [
			'lang' => ['required', 'string', Rule::in(config('app.supported_locale'))],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = self::ATTRIBUTE;
		$this->value = $values[self::ATTRIBUTE];
	}
}
