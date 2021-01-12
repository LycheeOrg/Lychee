<?php

namespace App\Actions\Albums;

use AccessControl;
use App\Actions\Albums\Extensions\PublicIds;
use App\Actions\Albums\Extensions\TopQuery;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Extensions\CustomSort;

class Tree
{
	use TopQuery;
	use CustomSort;

	/**
	 * @var string
	 */
	private $sortingCol;

	/**
	 * @var string
	 */
	private $sortingOrder;

	public function __construct()
	{
		$this->sortingCol = Configs::get_value('sorting_Albums_col');
		$this->sortingOrder = Configs::get_value('sorting_Albums_order');
	}

	public function get(): array
	{
		$return = [];
		$PublicIds = resolve(PublicIds::class);

		$sql = Album::initQuery()
			->where('smart', '=', false)
			->whereNotIn('id', $PublicIds->getNotAccessible())
			->orderBy('owner_id', 'ASC');
		$albumCollection = $this->customSort($sql, $this->sortingCol, $this->sortingOrder);

		if (AccessControl::is_logged_in()) {
			$id = AccessControl::id();
			list($albumCollection, $albums_shared) = $albumCollection->partition(fn ($album) => $album->owner_id == $id);
			$return['shared_albums'] = $this->prepare($albums_shared->toTree());
		}

		$return['albums'] = $this->prepare($albumCollection->toTree());

		return $return;
	}

	private function prepare($albums)
	{
		return $albums->map(function ($album) {
			$ret = [
				'id' => strval($album->id),
				'title' => $album->title,
				'parent_id' => strval($album->parent_id),
			];
			$album->set_thumbs($ret, $album->get_thumbs());
			$ret['albums'] = $this->prepare($album->children);

			return $ret;
		});
	}
}
