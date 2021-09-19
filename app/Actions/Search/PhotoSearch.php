<?php

namespace App\Actions\Search;

use App\Actions\PhotoAuthorisationProvider;
use App\Contracts\InternalLycheeException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PhotoSearch
{
	protected PhotoAuthorisationProvider $photoAuthorisationProvider;

	public function __construct()
	{
		$this->photoAuthorisationProvider = resolve(PhotoAuthorisationProvider::class);
	}

	/**
	 * @throws InternalLycheeException
	 */
	public function query(array $terms): Collection
	{
		$query = $this->photoAuthorisationProvider->applyVisibilityFilter(
			Photo::with(['album', 'size_variants_raw', 'size_variants_raw.sym_links'])
		);

		try {
			foreach ($terms as $term) {
				$query->where(
					fn (Builder $query) => $query
						->where('title', 'like', '%' . $term . '%')
						->orWhere('description', 'like', '%' . $term . '%')
						->orWhere('tags', 'like', '%' . $term . '%')
						->orWhere('location', 'like', '%' . $term . '%')
						->orWhere('model', 'like', '%' . $term . '%')
						->orWhere('taken_at', 'like', '%' . $term . '%')
				);
			}
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}

		return $query->get();
	}
}
