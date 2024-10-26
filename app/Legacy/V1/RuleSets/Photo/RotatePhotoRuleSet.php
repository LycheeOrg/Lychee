<?php

namespace App\Legacy\V1\RuleSets\Photo;

use App\Contracts\Http\RuleSet;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
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
