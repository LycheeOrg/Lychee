<?php

namespace App\Http\Requests\Album;

use App\DTO\PhotoSortingCriterion;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasBaseAlbum;
use App\Http\Requests\Contracts\HasSortingCriterion;
use App\Http\Requests\Contracts\RequestAttribute;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Http\Requests\Traits\HasSortingCriterionTrait;
use App\Rules\OrderRule;
use App\Rules\PhotoSortingRule;
use App\Rules\RandomIDRule;

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
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::SORTING_COLUMN_ATTRIBUTE => ['present', new PhotoSortingRule()],
			RequestAttribute::SORTING_ORDER_ATTRIBUTE => [
				'required_with:' . RequestAttribute::SORTING_COLUMN_ATTRIBUTE,
				new OrderRule(true),
			],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = $this->albumFactory->findBaseAlbumOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);
		$column = $values[RequestAttribute::SORTING_COLUMN_ATTRIBUTE];
		$this->sortingCriterion = $column === null ?
			null :
			new PhotoSortingCriterion($column, $values[RequestAttribute::SORTING_ORDER_ATTRIBUTE]);
	}
}
