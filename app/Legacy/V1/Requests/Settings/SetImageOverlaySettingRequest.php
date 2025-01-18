<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Settings;

use App\Enum\ImageOverlayType;
use Illuminate\Validation\Rules\Enum;

final class SetImageOverlaySettingRequest extends AbstractSettingRequest
{
	public const ATTRIBUTE = 'image_overlay_type';

	public function rules(): array
	{
		return [
			self::ATTRIBUTE => ['required', new Enum(ImageOverlayType::class)],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->name = self::ATTRIBUTE;
		$this->value = ImageOverlayType::from($values[self::ATTRIBUTE]);
	}
}
