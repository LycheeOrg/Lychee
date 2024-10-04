<?php

namespace App\Http\RuleSets\Photo;

use App\Contracts\Http\RuleSet;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Rules\RandomIDRule;

/**
 * Rules applied when fetching a photo.
 */
class GetPhotoRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
		];
	}
}
