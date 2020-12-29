<?php

namespace App\Actions\Albums;

use AccessControl;
use App\Models\Album;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;

trait PublicIds
{
	/**
	 * Build a query that remove all non public albums
	 * or public albums which are hidden
	 * or public albums with a password.
	 *
	 * @param  Builder
	 *
	 * @return Builder
	 */
	private function notPublicNotViewable(Builder $query): Builder
	{
		return $query
			// remove NOT public
			->where('public', '<>', '1')
			// or PUBLIC BUT NOT VIEWABLE (hidden)
			->orWhere(fn ($q) => $q->where('public', '=', '1')->where('viewable', '<>', '1'))
			// or PUBLIC BUT PASSWORD LOCKED
			->orWhere(fn ($q) => $q->where('public', '=', '1')->where('password', '<>', ''));
	}

	/**
	 * Return a collection of Album that are not directly accessible by visibility criteria
	 * ! we do not include password protected albums from other users.
	 *
	 * @return BaseCollection[Album]
	 */
	private function getDirectlyNotAccessible(): BaseCollection
	{
		if (AccessControl::is_admin()) {
			return new BaseCollection();
		}

		if (AccessControl::is_logged_in()) {
			$shared_ids = DB::table('user_album')->select('album_id')
				->where('user_id', '=', AccessControl::id())
				->pluck('album_id');

			return Album::where('owner_id', '<>', AccessControl::id())
				// shared are accessible
				->whereNotIn('id', $shared_ids)
				// remove NOT public
				->where(fn ($q) => $this->notPublicNotViewable($q))
				->get();
		}

		// remove NOT public
		return Album::where(fn ($q) => $this->notPublicNotViewable($q))
			->get();
	}

	/**
	 * Return an array of ids of albums that are not accessible.
	 *
	 * @return array[int]
	 */
	private function getNotAccessible(): BaseCollection
	{
		/**
		 * @var BaseCollection
		 */
		$directly = $this->getDirectlyNotAccessible();

		if ($directly->count() > 0) {
			$sql = Album::select('id');
			foreach ($directly as $alb) {
				$sql = $sql->orWhereBetween('_lft', [$alb->_lft, $alb->_rgt]);
			}

			return $sql->pluck('id');
		}

		return new BaseCollection();
	}

	/**
	 * @param $toplevel optional return from getToplevelAlbums()
	 *
	 * @return Collection[int] of all recursive albums ID accessible by the current user from the top level
	 */
	public function getPublicAlbumsId(): BaseCollection
	{
		$id_not_accessible = $this->getNotAccessible();

		return Album::select('id')->whereNotIn('id', $id_not_accessible)->pluck('id');
	}
}
