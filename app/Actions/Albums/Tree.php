<?php

namespace App\Actions\Albums;

use App\Actions\AlbumAuthorisationProvider;
use App\Facades\AccessControl;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Extensions\SortingDecorator;
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
		$this->sortingCol = Configs::get_value('sorting_Albums_col', 'created_at');
		$this->sortingOrder = Configs::get_value('sorting_Albums_order', 'ASC');
	}

	public function get(): array
	{
		$return = [];

		/**
		 * Note, strictly speaking
		 * {@link AlbumAuthorisationProvider::applyBrowsabilityFilter()}
		 * would be the correct function in order to scope the query below,
		 * because we only want albums which are browsable.
		 * But
		 * {@link AlbumAuthorisationProvider::applyBrowsabilityFilter()}
		 * is rather slow for large sets of albums.
		 * Luckily, {@link AlbumAuthorisationProvider::applyVisibilityFilter()}
		 * is sufficient here, although it does only consider an album's
		 * visibility locally.
		 * We rely on `->toTree` below to remove albums which are not
		 * reachable.
		 *
		 * @var NsQueryBuilder $query
		 */
		$query = $this->albumAuthorisationProvider
			->applyVisibilityFilter(Album::query());

		if (AccessControl::is_logged_in()) {
			// For authenticated users we group albums by ownership.
			$albums = (new SortingDecorator($query))
				->orderBy('owner_id')
				->orderBy($this->sortingCol, $this->sortingOrder)
				->get();

			$id = AccessControl::id();
			/** @var NsCollection $sharedAlbums */
			list($albums, $sharedAlbums) = $albums->partition(fn ($album) => $album->owner_id == $id);
			$return['shared_albums'] = $this->toArray($sharedAlbums->toTree());
		} else {
			// For anonymous users we don't want to implicitly expose
			// ownership via sorting.
			$albums = (new SortingDecorator($query))
				->orderBy($this->sortingCol, $this->sortingOrder)
				->get();
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
