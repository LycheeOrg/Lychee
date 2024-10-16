<?php

namespace App\Legacy\V1\RuleSets\Album;

use App\Contracts\Http\RuleSet;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Rules\AlbumIDRule;

/**
 * Rules applied when working on an albumSet.
 */
class DeleteAlbumsRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::ALBUM_IDS_ATTRIBUTE . '.*' => ['required', new AlbumIDRule(false)],
		];
	}
}
