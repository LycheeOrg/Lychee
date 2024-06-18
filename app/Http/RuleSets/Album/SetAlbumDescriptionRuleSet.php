<?php

declare(strict_types=1);

namespace App\Http\RuleSets\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Rules\DescriptionRule;
use App\Rules\RandomIDRule;

/**
 * Rules applied when changing the description of an album.
 */
class SetAlbumDescriptionRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::DESCRIPTION_ATTRIBUTE => ['present', new DescriptionRule()],
		];
	}
}
