<?php

namespace App\Actions\Search;

use App\Actions\Albums\Extensions\PublicIds;
use App\Models\Album;
use App\Models\TagAlbum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

class AlbumSearch
{
	protected BaseCollection $publicAlbumIDs;

	public function __construct()
	{
		$this->publicAlbumIDs = resolve(PublicIds::class)->getPublicAlbumsId();
	}

	public function query(array $terms): Collection
	{
		$albums = $this->applySearchFilter($terms, TagAlbum::query())->get();
		$albums->push($this->applySearchFilter($terms, Album::query())->get());

		return $albums;
	}

	private function applySearchFilter(array $terms, Builder $query): Builder
	{
		$query->whereIn('id', $this->publicAlbumIDs);
		foreach ($terms as $term) {
			$query->where(
				fn (Builder $query) => $query->where('title', 'like', '%' . $term . '%')
					->orWhere('description', 'like', '%' . $term . '%')
			);
		}

		return $query;
	}
}
