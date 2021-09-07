<?php

namespace App\Actions\Albums;

use App\Actions\AlbumAuthorisationProvider;
use App\Facades\AccessControl;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Database\Eloquent\Collection;
use Kalnoy\Nestedset\Collection as NsCollection;
use Kalnoy\Nestedset\QueryBuilder as NsQueryBuilder;

class Tree
{
	private AlbumAuthorisationProvider $albumAuthorisationProvider;
	private string $sortingCol;
	private string $sortingOrder;

	public function __construct(AlbumAuthorisationProvider $albumAuthorisationProvider)
	{
		$this->albumAuthorisationProvider = $albumAuthorisationProvider;
		$this->sortingCol = Configs::get_value('sorting_Albums_col');
		$this->sortingOrder = Configs::get_value('sorting_Albums_order');
	}

	public function get(): array
	{
		$return = [];

		/** @var NsQueryBuilder $query */
		$query = $this->albumAuthorisationProvider
			->applyVisibilityFilter(Album::query());

		if (in_array($this->sortingCol, ['title', 'description'])) {
			/** @var NsCollection $albums */
			$albums = $query
				->orderBy('id', 'ASC')
				->get()
				->sortBy($this->sortingCol, SORT_NATURAL | SORT_FLAG_CASE, $this->sortingOrder === 'DESC');
		} else {
			/** @var NsCollection $albums */
			$albums = $query
				->orderBy($this->sortingCol, $this->sortingOrder)
				->orderBy('id', 'ASC')
				->get();
		}

		if (AccessControl::is_logged_in()) {
			$id = AccessControl::id();
			/** @var NsCollection $sharedAlbums */
			list($albums, $sharedAlbums) = $albums->partition(fn ($album) => $album->owner_id == $id);
			$return['shared_albums'] = $this->toArray($sharedAlbums->toTree());
		}

		$return['albums'] = $this->toArray($albums->toTree());

		return $return;
	}

	private function toArray(Collection $albums): array
	{
		return $albums->map(fn (Album $album) => [
			'id' => $album->id,
			'title' => $album->title,
			'thumb' => optional($album->thumb)->toArray(),
			'parent_id' => $album->parent_id,
			'albums' => $this->toArray($album->children),
		])->all();
	}
}
