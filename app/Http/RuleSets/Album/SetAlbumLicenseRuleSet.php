<?php

namespace App\Http\RuleSets\Album;

use App\Contracts\Http\RuleSet;
use App\Enum\LicenseType;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Rules\RandomIDRule;
use Illuminate\Validation\Rules\Enum;

/**
 * Rules applied when changing the license of an album.
 */
class SetAlbumLicenseRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::LICENSE_ATTRIBUTE => ['required', new Enum(LicenseType::class)],
		];
	}
}
