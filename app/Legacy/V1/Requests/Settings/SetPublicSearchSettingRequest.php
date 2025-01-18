<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Settings;

final class SetPublicSearchSettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'search_public';

	public function rules(): array
	{
		return [
			'public_search' => 'required_without:search_public|boolean', // legacy
			'search_public' => 'required_without:public_search|boolean', // new value
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = self::ATTRIBUTE;
		$this->value = self::toBoolean($values['search_public'] ?? $values['public_search']);
	}
}
