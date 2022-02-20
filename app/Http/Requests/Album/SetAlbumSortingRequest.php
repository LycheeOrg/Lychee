<?php

namespace App\Http\Requests\Album;

use App\DTO\PhotoSortingCriterion;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasBaseAlbum;
use App\Http\Requests\Contracts\HasSortingCriterion;
use App\Http\Requests\Traits\HasBaseAlbumTrait;
use App\Http\Requests\Traits\HasSortingCriterionTrait;
use App\Rules\OrderRule;
use App\Rules\PhotoSortingRule;
use App\Rules\RandomIDRule;

class SetAlbumSortingRequest extends BaseApiRequest implements HasBaseAlbum, HasSortingCriterion
{
	use HasBaseAlbumTrait;
	use HasSortingCriterionTrait;

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
			HasSortingCriterion::SORTING_COLUMN_ATTRIBUTE => ['present', new PhotoSortingRule()],
			HasSortingCriterion::SORTING_ORDER_ATTRIBUTE => [
				'required_with:' . HasSortingCriterion::SORTING_COLUMN_ATTRIBUTE,
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
		$column = $values[HasSortingCriterion::SORTING_COLUMN_ATTRIBUTE];
		$this->sortingCriterion = empty($column) ?
			null :
			new PhotoSortingCriterion($column, $values[HasSortingCriterion::SORTING_ORDER_ATTRIBUTE]);
	}
}
