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
		return $this->createTagAlbumQuery($terms)->get()->concat($this->createAlbumQuery($terms)->get());
	}

	private function createAlbumQuery($terms): Builder
	{
		$albumQuery = Album::query()
			->select(['albums.*'])
			->join('base_albums', 'base_albums.id', '=', 'albums.id');
		$this->addSearchCondition($terms, $albumQuery);
		$this->albumAuthorisationProvider->applyBrowsabilityFilter($albumQuery);

		return $albumQuery;
	}

	private function createTagAlbumQuery(array $terms): Builder
	{
		// Note: `applyVisibilityFilter` already adds a JOIN clause with `base_albums`.
		// No need to add a second JOIN clause.
		$albumQuery = $this->albumAuthorisationProvider->applyVisibilityFilter(
			TagAlbum::query()
		);
		$this->addSearchCondition($terms, $albumQuery);

		return $albumQuery;
	}

	private function addSearchCondition(array $terms, Builder $query): Builder
	{
		foreach ($terms as $term) {
			$query->where(
				fn (Builder $query) => $query
					->where('base_albums.title', 'like', '%' . $term . '%')
					->orWhere('base_albums.description', 'like', '%' . $term . '%')
			);
		}

		return $query;
	}
}
