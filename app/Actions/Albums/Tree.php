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
			// ATTENTION:
			// For this to work correctly, it is crucial that all child albums
			// below each top-level album have the same owner!
			// Otherwise, this partitioning tears apart albums of the same
			// (sub)-tree and then `toTree` will return garbage as it does
			// not find connected paths within `$albums` or `$sharedAlbums`,
			// resp.
			/** @var NsCollection $sharedAlbums */
			list($albums, $sharedAlbums) = $albums->partition(fn ($album) => $album->owner_id == $id);
			// We must explicitly pass `null` as the ID of the root album
			// as there are several top-level albums below root.
			// Otherwise, `toTree` uses the ID of the album with the lowest
			// `_lft` value as the (wrong) root album.
			$return['shared_albums'] = $this->toArray($sharedAlbums->toTree(null));
		} else {
			// For anonymous users we don't want to implicitly expose
			// ownership via sorting.
			$albums = (new SortingDecorator($query))
				->orderBy($this->sortingCol, $this->sortingOrder)
				->get();
		}

		// We must explicitly pass `null` as the ID of the root album
		// as there are several top-level albums below root.
		// Otherwise, `toTree` uses the ID of the album with the lowest
		// `_lft` value as the (wrong) root album.
		$return['albums'] = $this->toArray($albums->toTree(null));

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
