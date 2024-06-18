<?php

declare(strict_types=1);

namespace App\Http\RuleSets\Photo;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Rules\AlbumIDRule;

/**
 * Rules applied when adding a new photo.
 */
class AddPhotoRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['present', new AlbumIDRule(true)],
			RequestAttribute::FILE_LAST_MODIFIED_TIME => 'sometimes|nullable|numeric',
			RequestAttribute::FILE_ATTRIBUTE => 'required|file',
		];
	}
}
