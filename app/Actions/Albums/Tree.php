<?php

namespace App\Actions\Albums;

use App\Actions\Albums\Extensions\PublicIds;
use App\Facades\AccessControl;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Database\Eloquent\Collection;
use Kalnoy\Nestedset\Collection as NsCollection;
use Kalnoy\Nestedset\QueryBuilder as NsQueryBuilder;

class Tree
{
	public function get(): array
	{
		$return = [];
		$publicIDs = resolve(PublicIds::class);
		$sortingCol = Configs::get_value('sorting_Albums_col');
		$sortingOrder = Configs::get_value('sorting_Albums_order');

		/** @var NsQueryBuilder $query */
		$query = Album::query()
			->whereNotIn('id', $publicIDs->getNotAccessible());

		if (in_array($sortingCol, ['title', 'description'])) {
			/** @var NsCollection $albums */
			$albums = $query
				->orderBy('id', 'ASC')
				->get()
				->sortBy($sortingCol, SORT_NATURAL | SORT_FLAG_CASE, $sortingOrder === 'DESC');
		} else {
			/** @var NsCollection $albums */
			$albums = $query
				->orderBy($sortingCol, $sortingOrder)
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
