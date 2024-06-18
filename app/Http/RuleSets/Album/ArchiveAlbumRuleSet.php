<?php

declare(strict_types=1);

namespace App\Http\RuleSets\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
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
