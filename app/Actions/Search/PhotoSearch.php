<?php

namespace App\Actions\Search;

use App\Contracts\Exceptions\InternalLycheeException;
use App\DTO\PhotoSortingCriterion;
use App\Eloquent\FixedQueryBuilder;
use App\Models\Album;
use App\Models\Extensions\SortingDecorator;
use App\Models\Photo;
use App\Policies\PhotoQueryPolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PhotoSearch
{
	protected PhotoQueryPolicy $photoQueryPolicy;

	public function __construct(PhotoQueryPolicy $photoQueryPolicy)
	{
		$this->photoQueryPolicy = $photoQueryPolicy;
	}

	/**
	 * Apply search directly.
	 *
	 * @param array<int,string> $terms
	 *
	 * @return Collection<int,Photo> photos
	 *
	 * @throws InternalLycheeException
	 */
	public function query(array $terms): Collection
	{
		$query = $this->sqlQuery($terms);
		$sorting = PhotoSortingCriterion::createDefault();

		return (new SortingDecorator($query))
			->orderBy($sorting->column, $sorting->order)->get();
	}

	/**
	 * Create the query manually.
	 *
	 * @param array<int,string> $terms
	 * @param Album|null        $album the optional top album which is used as a search base
	 *
	 * @return FixedQueryBuilder<Photo>
	 */
	public function sqlQuery(array $terms, ?Album $album = null): Builder
	{
		$query = $this->photoQueryPolicy->applySearchabilityFilter(
			Photo::query()->with(['album', 'size_variants', 'size_variants.sym_links']),
			$album
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

		return $query;
	}
}
