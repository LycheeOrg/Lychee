<?php

namespace App\Http\Requests\Album;

use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasBaseAlbum;
use App\Http\Requests\Contracts\HasSortingCriterion;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Http\Requests\Traits\HasSortingCriterionTrait;
use App\Rules\RandomIDRule;
use Illuminate\Validation\Rules\Enum;

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
		return [
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			HasSortingCriterion::SORTING_COLUMN_ATTRIBUTE => ['present', 'nullable', new Enum(ColumnSortingPhotoType::class)],
			HasSortingCriterion::SORTING_ORDER_ATTRIBUTE => [
				'required_with:' . HasSortingCriterion::SORTING_COLUMN_ATTRIBUTE,
				'nullable', new Enum(OrderSortingType::class),
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->albumFactory->findBaseAlbumOrFail($values[HasAbstractAlbum::ALBUM_ID_ATTRIBUTE]);

		$column = ColumnSortingPhotoType::tryFrom($values[HasSortingCriterion::SORTING_COLUMN_ATTRIBUTE]);
		$order = OrderSortingType::tryFrom($values[HasSortingCriterion::SORTING_ORDER_ATTRIBUTE]);

		$this->sortingCriterion = $column === null ?
			null :
			new PhotoSortingCriterion($column->toColumnSortingType(), $order);
	}
}
