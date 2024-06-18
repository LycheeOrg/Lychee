<?php

declare(strict_types=1);

namespace App\Http\RuleSets\Photo;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Enum\DownloadVariantType;
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
