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
		$albums = $this->applySearchFilter($terms, TagAlbum::query())->get();
		$albums->push($this->applySearchFilter($terms, Album::query())->get());

		return $albums;
	}

	private function applySearchFilter(array $terms, Builder $query): Builder
	{
		$this->albumAuthorisationProvider->applyVisibilityFilter($query);
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
