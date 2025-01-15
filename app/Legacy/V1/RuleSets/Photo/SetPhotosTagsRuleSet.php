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

/**
 * Rules applied when adding tag to a group of photos.
 */
class SetPhotosTagsRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::SHALL_OVERRIDE_ATTRIBUTE => 'required|boolean',
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			RequestAttribute::TAGS_ATTRIBUTE => 'present|array',
			RequestAttribute::TAGS_ATTRIBUTE . '.*' => 'required|string|min:1',
		];
	}
}