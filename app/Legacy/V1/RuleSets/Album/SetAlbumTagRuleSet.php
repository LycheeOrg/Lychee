<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\RuleSets\Album;

use App\Contracts\Http\RuleSet;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Rules\RandomIDRule;

/**
 * Rules applied when changing the tag of a tag album.
 */
class SetAlbumTagRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::SHOW_TAGS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::SHOW_TAGS_ATTRIBUTE . '.*' => 'required|string|min:1',
		];
	}
}
