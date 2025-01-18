<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Settings;

final class SetDropboxKeySettingRequest extends AbstractSettingRequest
{
	public function rules(): array
	{
		return ['key' => 'present|string|nullable'];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = 'dropbox_key';
		$this->value = $values['key'];
	}
}
