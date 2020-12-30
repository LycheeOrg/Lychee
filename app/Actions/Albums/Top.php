<?php

namespace App\Actions\Albums;

use AccessControl;
use App\Models\Configs;
use App\Models\Extensions\CustomSort;
use Illuminate\Support\Collection as BaseCollection;

class Top
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

	/**
	 * Returns an array of top-level albums and shared albums visible to
	 * the current user.
	 * Note: the array may include password-protected albums that are not
	 * accessible (but are visible).
	 *
	 * @return array[Collection[Album]]
	 */
	public function get(): array
	{
		$return = [
			'albums' => new BaseCollection(),
			'shared_albums' => new BaseCollection(),
		];

		$sql = $this->createTopleveAlbumsQuery()->where('smart', '=', false);
		$albumCollection = $this->customSort($sql, $this->sortingCol, $this->sortingOrder);

		if (AccessControl::is_logged_in()) {
			$id = AccessControl::id();
			list($return['albums'], $return['shared_albums']) = $albumCollection->partition(function ($album) use ($id) {
				return $album->owner_id == $id;
			});
		} else {
			$return['albums'] = $albumCollection;
		}

		return $return;
	}
}
