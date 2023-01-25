<?php

namespace App\Http\RuleSets\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
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
