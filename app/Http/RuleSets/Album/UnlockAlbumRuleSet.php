<?php

namespace App\Http\RuleSets\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Rules\PasswordRule;
use App\Rules\RandomIDRule;

/**
 * Rules applied when unlocking a password protected album.
 */
class UnlockAlbumRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['required', new PasswordRule(false)],
		];
	}
}
