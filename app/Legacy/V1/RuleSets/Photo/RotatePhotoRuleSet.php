<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\RuleSets\Photo;

use App\Contracts\Http\RuleSet;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Rules\RandomIDRule;
use Illuminate\Validation\Rule;

/**
 * Rules applied when rotating a photo.
 */
class RotatePhotoRuleSet implements RuleSet
{
	protected int $direction;

	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::DIRECTION_ATTRIBUTE => ['required', Rule::in([-1, 1])],
		];
	}
}
