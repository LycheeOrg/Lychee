<?php

namespace App\Http\RuleSets\Import;

use App\Contracts\Http\RuleSet;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Rules\RandomIDRule;

/**
 * Rules applied when importing a file from the server.
 */
class ImportServerRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			RequestAttribute::PATH_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::PATH_ATTRIBUTE . '.*' => 'required|string|distinct',
			RequestAttribute::DELETE_IMPORTED_ATTRIBUTE => 'sometimes|boolean',
			RequestAttribute::SKIP_DUPLICATES_ATTRIBUTE => 'sometimes|boolean',
			RequestAttribute::IMPORT_VIA_SYMLINK_ATTRIBUTE => 'sometimes|boolean',
			RequestAttribute::RESYNC_METADATA_ATTRIBUTE => 'sometimes|boolean',
		];
	}
}
