<?php

namespace App\Actions\Sharing;

use App\DTO\Shares;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Extensions\BaseAlbum;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ListShare
{
	/**
	 * Returns a list of shares optionally filtered by the passed attributes.
	 *
	 * @param User|null      $participant the optional user who participates
	 *                                    in a sharing, i.e. the user with
	 *                                    whom albums are shared
	 * @param User|null      $owner       the optional owner of the albums
	 *                                    which are shared
	 * @param BaseAlbum|null $baseAlbum   the optional album which is shared
	 *
	 * @return Shares
	 *
	 * @throws QueryBuilderException
	 */
	public function do(?User $participant, ?User $owner, ?BaseAlbum $baseAlbum): Shares
	{
		try {
			// Active shares, optionally filtered by album ID, participant ID
			// and or owner ID
			$shared_query = DB::table('user_base_album')
				->select([
					'user_base_album.id',
					'user_id',
					DB::raw('base_album_id as album_id'),
					'username',
					'title',
				])
				->join('users', 'user_id', '=', 'users.id')
				->join('base_albums', 'base_album_id', '=', 'base_albums.id');
			if ($participant !== null) {
				$shared_query->where('user_base_album.user_id', '=', $participant->id);
			}
			if ($owner !== null) {
				$shared_query->where('base_albums.owner_id', '=', $owner->id);
			}
			if ($baseAlbum !== null) {
				$shared_query->where('base_albums.id', '=', $baseAlbum->id);
			}
			$shared = $shared_query
				->orderBy('title', 'ASC')
				->orderBy('username', 'ASC')
				->get();

			// Existing albums which can be shared optionally filtered by
			// album ID and/or owner ID
			$albums_query = DB::table('base_albums')
				->leftJoin('albums', 'albums.id', '=', 'base_albums.id')
				->select(['base_albums.id', 'title', 'parent_id'])
				->orderBy('title', 'ASC');
			if ($owner !== null) {
				$albums_query->where('owner_id', '=', $owner->id);
			}
			if ($baseAlbum !== null) {
				$albums_query->where('base_albums.id', '=', $baseAlbum->id);
			}
			$albums = $albums_query->get();
			$this->linkAlbums($albums);
			$albums->each(function ($album) {
				$album->title = $this->breadcrumbPath($album);
			});
			$albums->each(function ($album) {
				unset($album->parent_id);
				unset($album->parent);
			});

			// Existing users with whom an album can be shared optionally
			// filtered by participant ID
			$users_query = DB::table('users')->select(['id', 'username']);
			if ($participant !== null) {
				$users_query->where('id', '=', $participant->id);
			} else {
				$users_query->where('id', '>', 0);
			}
			$users = $users_query->orderBy('username', 'ASC')
				->get()
				->each(function ($user) {
					$user->id = intval($user->id);
				});

			return new Shares($shared, $albums, $users);
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}
	}

	/**
	 * Creates the breadcrumb path of an album.
	 *
	 * @param \App\Models\Album $album this is not really an album but a very
	 *                                 stripped down version of an album with
	 *                                 only the following properties:
	 *                                 `title`, `parent` and `parent_id` (unused here)
	 *
	 * @return string the breadcrumb path
	 */
	private function breadcrumbPath(object $album): string
	{
		$title = [$album->title];
		$parent = $album->parent;
		while ($parent) {
			array_unshift($title, $parent->title);
			$parent = $parent->parent;
		}

		return implode('/', $title);
	}

	private function linkAlbums(Collection $albums): void
	{
		if ($albums->isEmpty()) {
			return;
		}

		$groupedAlbums = $albums->groupBy('parent_id');

		foreach ($albums as $album) {
			// We must ensure that for each album the property `parent` is
			// defined as `breadcrumbPath` accesses this property.
			// At the same time, we must not _unconditionally_ initialize this
			// property with `null`, as the `parent` property might already
			// have been set to its final value in case the parent of current
			// object has already been processed earlier and has initialized
			// the property (see `foreach` below).
			// Keep in mind that the order of albums is arbitrary, hence
			// we cannot guarantee whether parents are processed before its
			// children or vice versa.
			// However, we must not use `$album->parent_id !== null` to check
			// whether there is such a parent object eventually.
			// An album may have a parent (i.e. `$album->parent_id !== null`
			// holds), but the parent might not be part of the result set,
			// if the query has been restricted to a particular album and
			// the album tree has become disintegrated into a forest of
			// subtrees.
			if (!isset($album->parent)) {
				$album->parent = null;
			}
			$childAlbums = $groupedAlbums->get($album->id, []);
			foreach ($childAlbums as $childAlbum) {
				$childAlbum->parent = $album;
			}
		}
	}
}
