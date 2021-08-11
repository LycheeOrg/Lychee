<?php

namespace App\Actions\Albums;

use App\Actions\Albums\Extensions\PublicIds;
use App\Facades\AccessControl;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Support\Collection as BaseCollection;
use Kalnoy\Nestedset\Collection as NsCollection;
use Kalnoy\Nestedset\QueryBuilder as NsQueryBuilder;

class Top
{
	/**
	 * Returns an array of top-level albums (but not tag albums) visible
	 * to the current user.
	 *
	 * If the user is authenticated, then the result differentiates between
	 * albums which are owned by the user and "shared" albums which the
	 * user does not own, but is allowed to see.
	 * The term "shared album" might be a little bit misleading here.
	 * Albums which are owned by the user himself may also be shared (with
	 * other users.)
	 * Actually, in this context "shared albums" means "foreign albums".
	 *
	 * Note, the array may include password-protected albums that are not
	 * accessible (but are visible).
	 *
	 * @return array
	 */
	public function get(): array
	{
		$return = [
			'albums' => new BaseCollection(),
			'shared_albums' => new BaseCollection(),
		];
		$publicIDs = resolve(PublicIds::class);
		$sortingCol = Configs::get_value('sorting_Albums_col');
		$sortingOrder = Configs::get_value('sorting_Albums_order');

		/** @var NsQueryBuilder $query */
		$query = Album::query();
		// TODO: Figure out, why `Tree` removes IDs which are returned by `getNotAccessible`, but here we use `publicViewable`
		$query = $publicIDs->publicViewable($query->whereIsRoot());

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
			list($return['albums'], $return['shared_albums']) = $albums->partition(fn ($album) => $album->owner_id == $id);
		} else {
			$return['albums'] = $albums;
		}

		return $return;
	}
}
