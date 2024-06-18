<?php

declare(strict_types=1);

namespace App\Http\RuleSets\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Rules\AlbumIDRule;
use App\Rules\RandomIDRule;

/**
 * Rules applied when merging albums.
 */
class MoveAlbumsRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::ALBUM_IDS_ATTRIBUTE . '.*' => ['required', new AlbumIDRule(false)],
		];
	}
}
