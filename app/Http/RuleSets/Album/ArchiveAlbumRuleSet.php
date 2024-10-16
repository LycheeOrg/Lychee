<?php

namespace App\Http\RuleSets\Album;

use App\Contracts\Http\RuleSet;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Rules\AlbumIDListRule;

/**
 * Rules applied when Zipping an album.
 */
class ArchiveAlbumRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => ['required', new AlbumIDListRule()],
		];
	}
}
