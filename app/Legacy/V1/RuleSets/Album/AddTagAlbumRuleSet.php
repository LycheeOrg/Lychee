<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\RuleSets\Album;

use App\Contracts\Http\RuleSet;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Rules\TitleRule;

/**
 * Rules applied when creating a tag album.
 */
class AddTagAlbumRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
			RequestAttribute::TAGS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::TAGS_ATTRIBUTE . '.*' => 'required|string|min:1',
		];
	}
}
