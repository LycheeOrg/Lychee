<?php

declare(strict_types=1);

namespace App\Http\RuleSets\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Rules\RandomIDRule;
use App\Rules\TitleRule;

/**
 * Rules applied when creating an album.
 */
class AddAlbumRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::PARENT_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
		];
	}
}
