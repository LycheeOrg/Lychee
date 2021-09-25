<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Contracts\HasAlbumModelID;
use App\Http\Requests\Contracts\HasSortingColumn;
use App\Http\Requests\Contracts\HasSortingOrder;
use App\Http\Requests\Traits\HasAlbumModelIDTrait;
use App\Http\Requests\Traits\HasSortingColumnTrait;
use App\Http\Requests\Traits\HasSortingOrderTrait;
use App\Rules\ModelIDRule;
use App\Rules\SortingRule;
use Illuminate\Validation\Rule;

class SetAlbumSortingRequest extends BaseApiRequest implements HasAlbumModelID, HasSortingColumn, HasSortingOrder
{
	use HasAlbumModelIDTrait;
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
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['required', new ModelIDRule(false)],
			HasSortingColumn::SORTING_COLUMN_ATTRIBUTE => ['present', new SortingRule()],
			HasSortingOrder::SORTING_ORDER_ATTRIBUTE => [
				'required_with:' . HasSortingColumn::SORTING_COLUMN_ATTRIBUTE,
				Rule::in([null, 'ASC', 'DESC']),
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = intval($values[HasAlbumID::ALBUM_ID_ATTRIBUTE]);
		$this->sortingColumn = $values[HasSortingColumn::SORTING_COLUMN_ATTRIBUTE];
		$this->sortingOrder = $values[HasSortingOrder::SORTING_ORDER_ATTRIBUTE];
	}
}
