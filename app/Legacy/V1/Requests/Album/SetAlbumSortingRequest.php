<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Album;

use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasBaseAlbum;
use App\Legacy\V1\Contracts\Http\Requests\HasSortingCriterion;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasBaseAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasSortingCriterionTrait;
use App\Rules\RandomIDRule;
use Illuminate\Validation\Rules\Enum;

final class SetAlbumSortingRequest extends BaseApiRequest implements HasBaseAlbum, HasSortingCriterion
{
	use HasBaseAlbumTrait;
	use HasSortingCriterionTrait;
	use AuthorizeCanEditAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
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

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->albumFactory->findBaseAlbumOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);

		$column = ColumnSortingPhotoType::tryFrom($values[RequestAttribute::SORTING_COLUMN_ATTRIBUTE]);
		$order = OrderSortingType::tryFrom($values[RequestAttribute::SORTING_ORDER_ATTRIBUTE]);

		$this->sortingCriterion = $column === null ?
			null :
			new PhotoSortingCriterion($column->toColumnSortingType(), $order);
	}
}
