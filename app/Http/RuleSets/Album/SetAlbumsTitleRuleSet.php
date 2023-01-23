<?php

namespace App\Http\RuleSets\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Rules\RandomIDRule;
use App\Rules\TitleRule;

/**
 * Rules applied when updating the titles of a group of album.
 */
class SetAlbumsTitleRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::ALBUM_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
		];
	}
}
