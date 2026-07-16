<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Search;

use App\Actions\Search\SearchTokenParser;
use App\Contracts\Http\Requests\HasAbstractAlbum;
use App\Contracts\Http\Requests\HasAlbumSortingCriterion;
use App\Contracts\Http\Requests\HasPhotoSortingCriterion;
use App\Contracts\Http\Requests\HasSearchTokens;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\AlbumSortingCriterion;
use App\DTO\PhotoSortingCriterion;
use App\Enum\OrderSortingType;
use App\Enum\SearchSortingType;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Http\Requests\Traits\HasAlbumSortingCriterionTrait;
use App\Http\Requests\Traits\HasPhotoSortingCriterionTrait;
use App\Http\Requests\Traits\HasSearchTokensTrait;
use App\Policies\AlbumPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;
use function Safe\base64_decode;

class GetSearchRequest extends BaseApiRequest implements HasAbstractAlbum, HasSearchTokens, HasPhotoSortingCriterion, HasAlbumSortingCriterion
{
	use HasSearchTokensTrait;
	use HasAbstractAlbumTrait;
	use HasPhotoSortingCriterionTrait;
	use HasAlbumSortingCriterionTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		if (!Auth::check() && !$this->configs()->getValueAsBool('search_public')) {
			return false;
		}

		return Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $this->album]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::TERM_ATTRIBUTE => ['required', 'string'],
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['sometimes', new RandomIDRule(true)],
			RequestAttribute::SORTING_COLUMN_ATTRIBUTE => ['sometimes', 'nullable', new Enum(SearchSortingType::class)],
			RequestAttribute::SORTING_ORDER_ATTRIBUTE => [
				'required_with:' . RequestAttribute::SORTING_COLUMN_ATTRIBUTE,
				'nullable',
				new Enum(OrderSortingType::class),
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$album_id = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE] ?? null;
		$this->album = $this->album_factory->findNullalbleAbstractAlbumOrFail($album_id);

		$raw = base64_decode($values[RequestAttribute::TERM_ATTRIBUTE], true);
		$this->tokens = SearchTokenParser::parse($raw);

		$sorting_column = SearchSortingType::tryFrom($values[RequestAttribute::SORTING_COLUMN_ATTRIBUTE] ?? '');
		$sorting_order = OrderSortingType::tryFrom($values[RequestAttribute::SORTING_ORDER_ATTRIBUTE] ?? '') ?? OrderSortingType::ASC;

		$this->photo_sorting_criterion = $sorting_column === null ? null : new PhotoSortingCriterion(
			$sorting_column->toPhotoColumn()->toColumnSortingType(),
			$sorting_order,
		);
		$this->album_sorting_criterion = $sorting_column === null ? null : new AlbumSortingCriterion(
			$sorting_column->toAlbumColumn($sorting_order)->toColumnSortingType(),
			$sorting_order,
		);
	}
}