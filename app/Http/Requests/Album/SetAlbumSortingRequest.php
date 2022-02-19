<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasBaseAlbum;
use App\Http\Requests\Contracts\HasSortingColumn;
use App\Http\Requests\Contracts\HasSortingOrder;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Http\Requests\Traits\HasSortingColumnTrait;
use App\Http\Requests\Traits\HasSortingOrderTrait;
use App\Rules\OrderRule;
use App\Rules\PhotoSortingRule;
use App\Rules\RandomIDRule;

class SetAlbumSortingRequest extends BaseApiRequest implements HasBaseAlbum, HasSortingColumn, HasSortingOrder
{
	use HasBaseAlbumTrait;
	use HasSortingColumnTrait;
	use HasSortingOrderTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite($this->album);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			HasSortingColumn::SORTING_COLUMN_ATTRIBUTE => ['present', new PhotoSortingRule()],
			HasSortingOrder::SORTING_ORDER_ATTRIBUTE => [
				'required_with:' . HasSortingColumn::SORTING_COLUMN_ATTRIBUTE,
				new OrderRule(true),
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->albumFactory->findBaseAlbumOrFail($values[HasAbstractAlbum::ALBUM_ID_ATTRIBUTE]);
		$this->sortingColumn = $values[HasSortingColumn::SORTING_COLUMN_ATTRIBUTE];
		$this->sortingOrder = $values[HasSortingOrder::SORTING_ORDER_ATTRIBUTE];
	}
}
