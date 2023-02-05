<?php

namespace App\Http\RuleSets\Photo;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Rules\LicenseRule;
use App\Rules\RandomIDRule;

/**
 * Rule applied when changing the license of a photo.
 */
class SetPhotoLicenseRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::LICENSE_ATTRIBUTE => ['required', new LicenseRule()],
		];
	}
}
