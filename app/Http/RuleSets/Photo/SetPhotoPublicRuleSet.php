<?php

namespace App\Http\RuleSets\Photo;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Rules\RandomIDRule;

/**
 * Rules applied when changing the visibility of a single photo.
 */
class SetPhotoPublicRuleSet implements RuleSet
{
	protected bool $isPublic = false;

	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::IS_PUBLIC_ATTRIBUTE => 'required|boolean',
		];
	}
}
