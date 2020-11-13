<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\ModelFunctions;

use App\ControllerFunctions\ReadAccessFunctions;
use App\ModelFunctions\AlbumActions\Cast as AlbumCast;
use App\Models\Album;
use App\Models\Configs;
use App\SmartAlbums\SmartFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AlbumsFunctions
{
	/**
	 * @var readAccessFunctions
	 */
	private $readAccessFunctions;

	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

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
	 * AlbumFunctions constructor.
	 *
	 * @param SessionFunctions    $sessionFunctions
	 * @param ReadAccessFunctions $readAccessFunctions
	 * @param SymLinkFunctions    $symLinkFunctions
	 */
	public function __construct(SessionFunctions $sessionFunctions, ReadAccessFunctions $readAccessFunctions, AlbumFunctions $albumFunctions, SymLinkFunctions $symLinkFunctions, SmartFactory $smartFactory)
	{
		$this->sessionFunctions = $sessionFunctions;
		$this->readAccessFunctions = $readAccessFunctions;
		$this->albumFunctions = $albumFunctions;
		$this->symLinkFunctions = $symLinkFunctions;
		$this->smartFactory = $smartFactory;
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

			if ($this->sessionFunctions->is_logged_in()) {
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

		foreach ($this->getTagAlbums() as $tagAlbum) {
			$smartAlbums->push($tagAlbum);
		}

		$can_see_smart = $this->sessionFunctions->is_logged_in() && $this->sessionFunctions->can_upload();

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
		$albumIDs = new BaseCollection();
		/*
		 * @var Collection[Album]
		 */
		$toplevel = $toplevel ?? $this->getToplevelAlbums();
		if ($toplevel === null) {
			return $albumIDs;
		}
		$children = $children ?? $this->get_children($toplevel, $includePassProtected);

		$kinds = ['albums', 'shared_albums'];

		foreach ($kinds as $kind) {
			$toplevel[$kind]->each(function ($album) use (&$albumIDs, $includePassProtected) {
				$haveAccess = $this->readAccessFunctions->album($album, true);

				if ($haveAccess === 1 || ($includePassProtected && $haveAccess === 3)) {
					$albumIDs->push($album->id);
				}
			});
			$children[$kind]->each(function ($child) use (&$albumIDs) {
				$albumIDs = $albumIDs->concat($this->albumFunctions->flatMap_id($child));
			});
		}

		return $albumIDs;
	}

	/**
	 * Returns an array of top-level albums and shared albums visible to
	 * the current user.
	 * Note: the array may include password-protected albums that are not
	 * accessible (but are visible).
	 *
	 * @return array[Collection[Album]]
	 */
	public function getToplevelAlbums(): array
	{
		$return = [
			'albums' => new BaseCollection(),
			'shared_albums' => new BaseCollection(),
		];

		$sortingCol = Configs::get_value('sorting_Albums_col');
		$sortingOrder = Configs::get_value('sorting_Albums_order');

		$sql = $this->createTopleveAlbumsQuery()->where('smart', '=', false);
		$albumCollection = $this->albumFunctions->customSort($sql, $sortingCol, $sortingOrder);

		if ($this->sessionFunctions->is_logged_in()) {
			$id = $this->sessionFunctions->id();
			list($return['albums'], $return['shared_albums']) = $albumCollection->partition(function ($album) use ($id) {
				return $album->owner_id == $id;
			});
		} else {
			$return['albums'] = $albumCollection;
		}

		return $return;
	}

	private function createTopleveAlbumsQuery(): Builder
	{
		if ($this->sessionFunctions->is_logged_in()) {
			$sql = Album::with([
				'owner',
			])->where('parent_id', '=', null);

			$id = $this->sessionFunctions->id();

			if ($id > 0) {
				$sql = $sql->where(function ($query) use ($id) {
					$query = $query->where('owner_id', '=', $id);
					$query = $query->orWhereIn('id', DB::table('user_album')->select('album_id')
						->where('user_id', '=', $id));
					$query = $query->orWhere(function ($_query) {
						$_query->where('public', '=', true)->where('visible_hidden', '=', true);
					});
				});
			}

			return $sql->orderBy('owner_id', 'ASC');
		}

		return Album::where('public', '=', '1')
			->where('visible_hidden', '=', '1')
			->where('parent_id', '=', null);
	}

	public function getTagAlbums(): Collection
	{
		$sortingCol = Configs::get_value('sorting_Albums_col');
		$sortingOrder = Configs::get_value('sorting_Albums_order');

		$sql = $this->createTopleveAlbumsQuery()->where('smart', '=', true);

		return $this->albumFunctions->customSort($sql, $sortingCol, $sortingOrder)
			->map(function (Album $album) {
				return AlbumCast::toTagAlbum($album);
			});
	}

	/**
	 * Provided an password and an album, check if the album can be
	 * unlocked. If yes, unlock all albums with the same password.
	 */
	public function unlockAlbum(string $albumid, string $password)
	{
		switch ($albumid) {
			case 'starred':
			case 'public':
			case 'recent':
			case 'unsorted':
				return 'false';
			default:
				$album = Album::find($albumid);
				if ($album === null) {
					return 'false';
				}
				if ($album->public == 1) {
					if ($album->password === '') {
						return 'true';
					}
					if ($this->sessionFunctions->has_visible_album($album->id)) {
						return 'true';
					}
					if (Hash::check($password, $album->password)) {
						$this->unlockAllAlbums($password);

						return 'true';
					}
				}

				return 'false';
		}
	}

	/**
	 * Provided an password, add all the albums that the password unlocks.
	 */
	public function unlockAllAlbums(string $password)
	{
		// We add all the albums that the password unlocks so that the
		// user is not repeatedly asked to enter the password as they
		// browse through the hierarchy.  This should be safe as the
		// list of such albums is not exposed to the user and is
		// considered as the last access check criteria.
		$albums = Album::whereNotNull('password')->where('password', '!=', '')->get();
		$albumIDs = [];
		foreach ($albums as $album) {
			if (Hash::check($password, $album->password)) {
				$albumIDs[] = $album->id;
			}
		}
		$this->sessionFunctions->add_visible_albums($albumIDs);
	}
}
