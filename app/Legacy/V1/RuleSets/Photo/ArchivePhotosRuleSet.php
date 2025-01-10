<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\RuleSets\Photo;

use App\Contracts\Http\RuleSet;
use App\Enum\DownloadVariantType;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Rules\RandomIDListRule;
use Illuminate\Validation\Rules\Enum;

/**
 * Rules applied when downloading one or multiple photo.
 */
class ArchivePhotosRuleSet implements RuleSet
{
	/**
	 * {@inheritDoc}
	 */
	public static function rules(): array
	{
		return [
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => ['required', new RandomIDListRule()],
			RequestAttribute::SIZE_VARIANT_ATTRIBUTE => ['required', new Enum(DownloadVariantType::class)],
		];
	}
}
