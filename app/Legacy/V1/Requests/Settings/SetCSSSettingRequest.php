<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Settings;

final class SetCSSSettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'css';

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
		$this->name = self::ATTRIBUTE;
		$this->value = $values[self::ATTRIBUTE] ?? '';
	}
}
