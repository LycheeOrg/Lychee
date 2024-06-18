<?php

declare(strict_types=1);

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasBaseAlbum;
use App\Contracts\Http\Requests\HasSortingCriterion;
use App\Contracts\Http\Requests\RequestAttribute;
use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Http\Requests\Traits\HasSortingCriterionTrait;
use App\Http\RuleSets\Album\SetAlbumSortingRuleSetLegacy;

class SetAlbumSortingRequest extends BaseApiRequest implements HasBaseAlbum, HasSortingCriterion
{
	use HasBaseAlbumTrait;
	use HasSortingCriterionTrait;
	use AuthorizeCanEditAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return SetAlbumSortingRuleSetLegacy::rules();
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
