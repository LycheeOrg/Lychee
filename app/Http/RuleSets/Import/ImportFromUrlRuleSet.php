<?php

namespace App\Http\RuleSets\Import;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Rules\RandomIDRule;

/**
 * Rules applied when importing a file from the server.
 */
class ImportFromUrlRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			RequestAttribute::URLS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::URLS_ATTRIBUTE . '.*' => 'required|string',
		];
	}
}
