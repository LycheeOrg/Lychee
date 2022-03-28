<?php

namespace App\Rules;

use App\Actions\Photo\Archive;
use Illuminate\Contracts\Validation\Rule;

/**
 * SizeVariantRule.
 *
 * Rule to check whether the given attribute represents a valid size variant
 * usable for archiving.
 * Note that there are two different ways how to express the type of size
 * variant:
 *  - as an integer (cp. {@link \App\Models\SizeVariant}),
 *  - as a string with small letters (cp. {@link \App\Models\Extensions\SizeVariants::NAMES}), and
 *  - as a string with all caps (cp. {@link Archive::VARIANTS}).
 * Moreover, there are differences how the original variant is called
 * (e.g. `'original'` vs. `'FULL'`) and wrt. to archiving the live photo
 * counts as an independent, 8th extra size variant.
 *
 * TODO: Maybe the this should made consistent (also in the front-end) when we migrate to PHP 8 as PHP 8 supports real enums.
 */
class SizeVariantRule implements Rule
{
	/**
	 * {@inheritDoc}
	 */
	public function passes($attribute, $value): bool
	{
		return
			is_string($value) &&
			array_search($value, Archive::VARIANTS, true) !== false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function message(): string
	{
		return ':attribute must be one out of ' .
			implode(', ', Archive::VARIANTS);
	}
}
