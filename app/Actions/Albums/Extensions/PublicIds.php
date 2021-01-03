<?php

namespace App\Actions\Albums\Extensions;

use AccessControl;
use App\Models\Album;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;

class PublicIds
{
	/** @var BaseCollection */
	private $white_list = null;
	/** @var BaseCollection */
	private $black_list = null;

	public function __construct()
	{
		$this->initNotAccessible();
		$this->initPublicAlbumId();
	}

	/*------------------------------------------------------------------------------- */
	/**
	 * Initialize.
	 */

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
				// unlocked albums
				->whereNotIn('id', AccessControl::get_visible_albums())
				// remove NOT public
				->where(fn ($q) => $this->notPublicNotViewable($q))
				->get();
		}

		// remove NOT public
		return Album::whereNotIn('id', AccessControl::get_visible_albums())
			->where(fn ($q) => $this->notPublicNotViewable($q))
			->get();
	}

	private function initNotAccessible(): BaseCollection
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

			$this->black_list = $sql->pluck('id');

			return $this->black_list;
		}

		$this->black_list = new BaseCollection();

		return $this->black_list;
	}

	private function initPublicAlbumId(): BaseCollection
	{
		$id_not_accessible = $this->getNotAccessible();
		$this->white_list = Album::select('id')->whereNotIn('id', $id_not_accessible)->pluck('id');

		return $this->white_list;
	}

	/*------------------------------------------------------------------------------- */
	/**
	 * Getters.
	 */

	/**
	 * @return Collection[int] of all recursive albums ID accessible by the current user from the top level
	 */
	public function getPublicAlbumsId(): BaseCollection
	{
		return $this->white_list ?? $this->initPublicAlbumId();
	}

	/**
	 * Return an array of ids of albums that are not accessible.
	 *
	 * @return array[int]
	 */
	public function getNotAccessible(): BaseCollection
	{
		return $this->black_list ?? $this->initNotAccessible();
	}

	public function refresh()
	{
		$this->white_list = null;
		$this->black_list = null;
	}
}
