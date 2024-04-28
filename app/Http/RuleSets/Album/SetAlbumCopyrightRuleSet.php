<?php

namespace App\Http\RuleSets\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Rules\CopyrightRule;
use App\Rules\RandomIDRule;

/**
 * Rules applied when updating the copyright of an album.
 */
class SetAlbumCopyrightRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::COPYRIGHT_ATTRIBUTE => ['required', new CopyrightRule()],
		];
	}
}
