<?php

namespace App\Actions\Search;

use App\Actions\PhotoAuthorisationProvider;
use App\Contracts\InternalLycheeException;
use App\DTO\PhotoSortingCriterion;
use App\Models\Extensions\FixedQueryBuilder;
use App\Models\Extensions\SortingDecorator;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;

class PhotoSearch
{
	protected PhotoAuthorisationProvider $photoAuthorisationProvider;

	public function __construct(PhotoAuthorisationProvider $photoAuthorisationProvider)
	{
		$this->photoAuthorisationProvider = $photoAuthorisationProvider;
	}

	/**
	 * @throws InternalLycheeException
	 */
	public function query(array $terms): Collection
	{
		$query = $this->photoAuthorisationProvider->applySearchabilityFilter(
			Photo::with(['album', 'size_variants', 'size_variants.sym_links'])
		);

		foreach ($terms as $term) {
			$query->where(
				fn (FixedQueryBuilder $query) => $query
					->where('title', 'like', '%' . $term . '%')
					->orWhere('description', 'like', '%' . $term . '%')
					->orWhere('tags', 'like', '%' . $term . '%')
					->orWhere('location', 'like', '%' . $term . '%')
					->orWhere('model', 'like', '%' . $term . '%')
					->orWhere('taken_at', 'like', '%' . $term . '%')
			);
		}

		$sorting = PhotoSortingCriterion::createDefault();

		return (new SortingDecorator($query))
			->orderBy($sorting->column, $sorting->order)
			->get();
	}
}
