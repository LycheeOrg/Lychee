<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions;

use AccessControl;
use App\Actions\Album\Cast as AlbumCast;
use App\Actions\Albums\Tag;
use App\Actions\Albums\Top;
use App\Actions\ReadAccessFunctions;
use App\Models\Album;
use App\SmartAlbums\SmartFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;

class AlbumsFunctions
{
	/**
	 * @var readAccessFunctions
	 */
	private $readAccessFunctions;

	/**
	 * @var AlbumFunctions
	 */
	private $albumFunctions;

	/**
	 * @var SymLinkFunctions
	 */
	private $symLinkFunctions;

	/**
	 * @var SmartFactory
	 */
	private $smartFactory;

	/**
	 * @var Top
	 */
	private $top;

	/**
	 * @var Tag
	 */
	private $tag;

	/**
	 * AlbumFunctions constructor.
	 *
	 * @param SessionFunctions    $sessionFunctions
	 * @param ReadAccessFunctions $readAccessFunctions
	 * @param SymLinkFunctions    $symLinkFunctions
	 */
	public function __construct(ReadAccessFunctions $readAccessFunctions, AlbumFunctions $albumFunctions, SymLinkFunctions $symLinkFunctions, SmartFactory $smartFactory, Top $top, Tag $tag)
	{
		$this->readAccessFunctions = $readAccessFunctions;
		$this->albumFunctions = $albumFunctions;
		$this->symLinkFunctions = $symLinkFunctions;
		$this->smartFactory = $smartFactory;
		$this->top = $top;
		$this->tag = $tag;
	}

	/**
	 * ? Only used in AlbumsController
	 * Given a list of albums, generate an array to be returned.
	 *
	 * @param Collection[Album]        $albums
	 * @param Collection[Collection[]] $children
	 *
	 * @return array
	 */
	public function prepare_albums(BaseCollection $albums, BaseCollection $children)
	{
		$return = [];
		foreach ($albums->keys() as $key) {
			$album_array = AlbumCast::toArrayWith($albums[$key], $children[$key]);

			if (AccessControl::is_logged_in()) {
				$album_array['owner'] = $albums[$key]->owner->username;
			}

			if ($this->readAccessFunctions->album($albums[$key]) === 1) {
				$thumbs = $this->albumFunctions->get_thumbs($albums[$key], $children[$key]);
				$this->albumFunctions->set_thumbs($album_array, $thumbs);
				$this->albumFunctions->set_thumbs_children($album_array['albums'], $thumbs[1]);
			}

			// Add to return
			$return[] = $album_array;
		}

		return $return;
	}

	/**
	 * @param array[Collection[Album]] $albums_list
	 *
	 * @return array
	 */
	public function get_children(array $albums_list, $includePassProtected = false)
	{
		$return = [];
		foreach ($albums_list as $kind => $albums) {
			$return[$kind] = new BaseCollection();

			$albums->each(function ($album, $key) use ($return, $kind, $includePassProtected) {
				$children = new Collection();

				if ($this->readAccessFunctions->album($album) === 1) {
					$children = $this->albumFunctions->get_children($album, 0, $includePassProtected);
				}

				$return[$kind]->put($key, $children);
			});
		}

		return $return;
	}

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
		} elseif (AccessControl::is_logged_in()) {
			$shared_ids = DB::table('user_album')->select('album_id')
				->where('user_id', '=', AccessControl::id())
				->pluck('album_id');
			// ->get()->map(fn ($v) => $v->album_id);

			return Album::where('owner_id', '<>', AccessControl::id())
				// shared are accessible
				->whereNotIn('id', $shared_ids)
				// remove NOT public
				->where(fn ($q) => $this->notPublicNotViewable($q))
				->get();
		} else {
			// remove NOT public
			Album::where(fn ($q) => $this->notPublicNotViewable($q))
				->get();
		}
	}

	/**
	 * Return an array of ids of albums that are not accessible.
	 *
	 * @return array[int]
	 */
	private function getNotAccessible(): array
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
			// return $sql->get()->pluck('id');
		}

		return [];
	}

	/**
	 * @param $toplevel optional return from getToplevelAlbums()
	 *
	 * @return array returns an array of smart albums or false on failure
	 */
	public function getSmartAlbums($toplevel = null, $children = null)
	{
		/**
		 * Initialize return var.
		 */
		$return = [];
		/**
		 * @var Collection[SmartAlbum]
		 */
		$publicAlbums = null;
		$smartAlbums = new BaseCollection();
		foreach ($this->smartFactory::$base_smarts as $smart_kind) {
			$smartAlbums->push($this->smartFactory->make($smart_kind));
		}

		foreach ($this->tag->get() as $tagAlbum) {
			$smartAlbums->push($tagAlbum);
		}

		$can_see_smart = AccessControl::is_logged_in() && AccessControl::can_upload();

		foreach ($smartAlbums as $smartAlbum) {
			if ($can_see_smart || $smartAlbum->is_public()) {
				$publicAlbums = $publicAlbums ?? $this->getPublicAlbumsId($toplevel, $children);
				$smartAlbum->setAlbumIDs($publicAlbums);
				$return[$smartAlbum->get_title()] = AlbumCast::toArray($smartAlbum);
				AlbumCast::getThumbs($return[$smartAlbum->get_title()], $smartAlbum, $this->symLinkFunctions);
			}
		}

		if (empty($return)) {
			return null;
		}

		return $return;
	}

	/**
	 * @param $toplevel optional return from getToplevelAlbums()
	 *
	 * @return Collection[int] of all recursive albums ID accessible by the current user from the top level
	 */
	public function getPublicAlbumsId($toplevel = null, $children = null, $includePassProtected = false): BaseCollection
	{
		$id_not_accessible = $this->getNotAccessible();

		return Album::select('id')->whereNotIn('id', $id_not_accessible)->pluck('id');
		// // get()->$albumIDs = new BaseCollection();
		// /*
		//  * @var Collection[Album]
		//  */
		// $toplevel = $toplevel ?? $this->top->get();
		// if ($toplevel === null) {
		// 	return $albumIDs;
		// }
		// // expensive here especially given we only wants the id.
		// $children = $children ?? $this->get_children($toplevel, $includePassProtected);

		// $kinds = ['albums', 'shared_albums'];

		// foreach ($kinds as $kind) {
		// 	$toplevel[$kind]->each(function ($album) use (&$albumIDs, $includePassProtected) {
		// 		$haveAccess = $this->readAccessFunctions->album($album, true);

		// 		if ($haveAccess === 1 || ($includePassProtected && $haveAccess === 3)) {
		// 			$albumIDs->push($album->id);
		// 		}
		// 	});
		// 	$children[$kind]->each(function ($child) use (&$albumIDs) {
		// 		$albumIDs = $albumIDs->concat($this->albumFunctions->flatMap_id($child));
		// 	});
		// }

		// return $albumIDs;
	}
}
