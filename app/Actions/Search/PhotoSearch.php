<?php

namespace App\Actions\Search;

use App\Actions\PhotoAuthorisationProvider;
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

	public function query(array $terms): Collection
	{
		$query = $this->photoAuthorisationProvider->applySearchabilityFilter(
			Photo::with(['album', 'size_variants_raw', 'size_variants_raw.sym_links'])
		);

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

		return $query->get();
	}
}
