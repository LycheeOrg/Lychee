<?php

namespace App\Http\RuleSets\Album;

use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Http\RuleSet;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Rules\RandomIDRule;
use Illuminate\Validation\Rules\Enum;

/**
 * Rules applied when changing the sorting mode inside an album.
 */
class SetAlbumSortingRuleSet implements RuleSet
{
	public static function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::SORTING_COLUMN_ATTRIBUTE => ['present', 'nullable', new Enum(ColumnSortingPhotoType::class)],
			RequestAttribute::SORTING_ORDER_ATTRIBUTE => [
				'required_with:' . RequestAttribute::SORTING_COLUMN_ATTRIBUTE,
				'nullable', new Enum(OrderSortingType::class),
			],
		];
	}
}
