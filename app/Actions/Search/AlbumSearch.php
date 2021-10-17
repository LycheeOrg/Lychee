<?php

namespace App\Actions\Search;

use App\Actions\AlbumAuthorisationProvider;
use App\Models\Album;
use App\Models\TagAlbum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AlbumSearch
{
	protected AlbumAuthorisationProvider $albumAuthorisationProvider;

	public function __construct()
	{
		$this->albumAuthorisationProvider = resolve(AlbumAuthorisationProvider::class);
	}

	public function query(array $terms): Collection
	{
		$albums = $this->createTagAlbumQuery($terms)->get();
		$albums->push($this->createAlbumQuery($terms)->get());

		return $albums;
	}

	private function createAlbumQuery($terms): Builder
	{
		$albumQuery = Album::query();
		$this->addSearchCondition($terms, $albumQuery);
		$this->albumAuthorisationProvider->applyBrowsabilityFilter($albumQuery);

		return $albumQuery;
	}

	private function createTagAlbumQuery(array $terms): Builder
	{
		$albumQuery = TagAlbum::query();
		$this->albumAuthorisationProvider->applyVisibilityFilter($albumQuery);
		$this->addSearchCondition($terms, $albumQuery);

		return $albumQuery;
	}

	private function addSearchCondition(array $terms, Builder $query): Builder
	{
		foreach ($terms as $term) {
			$query->where(
				fn (Builder $query) => $query
					->where('title', 'like', '%' . $term . '%')
					->orWhere('description', 'like', '%' . $term . '%')
			);
		}

		return $query;
	}
}
