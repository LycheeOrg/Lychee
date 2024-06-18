<?php

declare(strict_types=1);

namespace App\Http\RuleSets\Photo;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Rules\RandomIDRule;
use App\Rules\TitleRule;

/**
 * Rules applied when changing the title of a group of photos.
 */
class SetPhotosTitleRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
		];
	}
}
