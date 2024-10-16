<?php

namespace App\Legacy\V1\RuleSets\Album;

use App\Contracts\Http\RuleSet;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Rules\PasswordRule;
use App\Rules\RandomIDRule;

/**
 * Rules applied when changing the protection policies of an album.
 */
class SetAlbumProtectionPolicyRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::PASSWORD_ATTRIBUTE => ['sometimes', new PasswordRule(true)],
			RequestAttribute::IS_PUBLIC_ATTRIBUTE => 'required|boolean',
			RequestAttribute::IS_LINK_REQUIRED_ATTRIBUTE => 'required|boolean',
			RequestAttribute::IS_NSFW_ATTRIBUTE => 'required|boolean',
			RequestAttribute::GRANTS_DOWNLOAD_ATTRIBUTE => 'required|boolean',
			RequestAttribute::GRANTS_FULL_PHOTO_ACCESS_ATTRIBUTE => 'required|boolean',
		];
	}
}
