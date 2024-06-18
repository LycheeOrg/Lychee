<?php

declare(strict_types=1);

namespace App\Http\RuleSets\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Rules\RandomIDRule;

/**
 * Rules applied when Setting the header of an album.
 */
class SetAlbumHeaderRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(true)],
			RequestAttribute::PHOTO_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
		];
	}
}