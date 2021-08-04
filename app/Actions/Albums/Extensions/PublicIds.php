<?php

namespace App\Actions\Albums\Extensions;

use App\Facades\AccessControl;
use App\Models\Album;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;

class PublicIds
{
	private ?BaseCollection $forbidden_list = null;
	private ?Album $parent = null;

	public function __construct()
	{
		$this->initNotAccessible();
	}

	/*------------------------------------------------------------------------------- */
	/**
	 * Queries.
	 */

	/**
	 * Build a query that removes all non public albums
	 * or public albums which are hidden
	 * or public albums with a password.
	 *
	 * @param Builder $query
	 *
	 * @return Builder
	 */
	private function notPublicNotViewable(Builder $query): Builder
	{
		return $query
			// remove NOT public
			->where('base_albums.public', '=', false)
			// or PUBLIC BUT NOT VIEWABLE (hidden)
			->orWhere(fn (Builder $q) => $q
				->where('base_albums.public', '=', true)
				->where('base_albums.viewable', '=', false)
			)
			// or PUBLIC BUT PASSWORD LOCKED
			->orWhere(fn (Builder $q) => $q
				->where('base_albums.public', '=', true)
				->where('base_albums.password', '<>', '')
				->whereNotNull('base_albums.password')
			);
	}

	private function init(): Builder
	{
		// unlocked albums
		$query = DB::table('base_albums')
			->join('albums', 'base_albums.id', '=', 'albums.id')
			->select('albums._lft', 'albums._rgt')
			->whereNotIn('albums.id', AccessControl::get_visible_albums());

		if ($this->parent == null) {
			return $query;
		}

		// add descendant constraints.
		return $query
			->where('albums._lft', '>', $this->parent->_lft)
			->where('albums._rgt', '<', $this->parent->_rgt);
	}

	/**
	 * Return a collection of Album that are not directly accessible by visibility criteria
	 * ! we do not include password protected albums from other users.
	 *
	 * @return BaseCollection[(_lft, _rgt)]
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

			return $this->init()
				->where('base_albums.owner_id', '<>', AccessControl::id())
				// shared are accessible
				->whereNotIn('id', $shared_ids)
				// remove NOT public
				->where(fn ($q) => $this->notPublicNotViewable($q))
				->get();
		}

		// remove NOT public
		return $this->init()
			->where(fn ($q) => $this->notPublicNotViewable($q))
			->get();
	}

	/*------------------------------------------------------------------------------- */

	/**
	 * Initializers.
	 */
	private function initNotAccessible(?Album $parent = null): BaseCollection
	{
		$this->parent = $parent;

		/**
		 * @var BaseCollection
		 */
		$directly = $this->getDirectlyNotAccessible();

		if ($directly->count() > 0) {
			$sql = DB::table('albums')->select('id');
			foreach ($directly as $alb) {
				$sql = $sql->orWhereBetween('_lft', [$alb->_lft, $alb->_rgt]);
			}

			$this->forbidden_list = $sql->pluck('id');

			return $this->forbidden_list;
		}

		$this->forbidden_list = new BaseCollection();

		return $this->forbidden_list;
	}

	/*------------------------------------------------------------------------------- */
	/**
	 * Getters.
	 */

	/**
	 * This function must only be called from ROOT. In other words for:
	 * => smart albums
	 * => search
	 * => map
	 * => random
	 * => RSS.
	 *
	 * @return BaseCollection of all recursive albums ID accessible by the current user from the top level
	 */
	public function getPublicAlbumsId(): BaseCollection
	{
		$id_not_accessible = $this->getNotAccessible();

		return DB::table('albums')->select('id')->whereNotIn('id', $id_not_accessible)->pluck('id');
	}

	/**
	 * Return an array of ids of albums that are not accessible.
	 *
	 * @return BaseCollection
	 */
	public function getNotAccessible(): BaseCollection
	{
		return $this->forbidden_list ?? $this->initNotAccessible();
	}

	/**
	 * We need to refresh PublicIds in our test suite.
	 */
	public function refresh(): void
	{
		$this->forbidden_list = null;
	}

	/*------------------------------------------------------------------------------- */

	/**
	 * Setter.
	 */
	public function setAlbum(Album $album): void
	{
		if ($this->parent === $album) {
			return;
		}

		$this->initNotAccessible($album);
	}
}
