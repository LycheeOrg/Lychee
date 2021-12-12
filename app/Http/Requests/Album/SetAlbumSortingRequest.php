<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Contracts\HasSortingColumn;
use App\Http\Requests\Contracts\HasSortingOrder;
use App\Http\Requests\Traits\HasAlbumIDTrait;
use App\Http\Requests\Traits\HasSortingColumnTrait;
use App\Http\Requests\Traits\HasSortingOrderTrait;
use App\Rules\OrderRule;
use App\Rules\PhotoSortingRule;
use App\Rules\RandomIDRule;

class SetAlbumSortingRequest extends BaseApiRequest implements HasAlbumID, HasSortingColumn, HasSortingOrder
{
	use HasAlbumIDTrait;
	use HasSortingColumnTrait;
	use HasSortingOrderTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite([$this->albumID]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
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
		$this->albumID = $values[HasAlbumID::ALBUM_ID_ATTRIBUTE];
		$this->sortingColumn = $values[HasSortingColumn::SORTING_COLUMN_ATTRIBUTE];
		$this->sortingOrder = $values[HasSortingOrder::SORTING_ORDER_ATTRIBUTE];
	}
}
