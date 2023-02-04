<?php

namespace App\Http\RuleSets\Photo;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Rules\RandomIDRule;
use Illuminate\Validation\Rule;

/**
 * Rules applied when rotating a photo.
 */
class RotatePhotoRuleSet implements RuleSet
{
	protected int $direction;

	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::DIRECTION_ATTRIBUTE => ['required', Rule::in([-1, 1])],
		];
	}
}
