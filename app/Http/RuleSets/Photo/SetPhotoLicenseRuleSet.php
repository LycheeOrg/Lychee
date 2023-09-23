<?php

namespace App\Http\RuleSets\Photo;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Enum\LicenseType;
use App\Rules\RandomIDRule;
use Illuminate\Validation\Rules\Enum;

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
			RequestAttribute::LICENSE_ATTRIBUTE => ['required', new Enum(LicenseType::class)],
		];
	}
}
