<?php

namespace App\Actions\Sharing;

use App\DTO\Shares;
use App\Exceptions\Internal\QueryBuilderException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ListShare
{
	/**
	 * @param int $userId
	 *
	 * @return Shares
	 *
	 * @throws QueryBuilderException
	 */
	public function do(int $userId): Shares
	{
		try {
			// prepare query
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

			$albums_query = DB::table('base_albums')
				->leftJoin('albums', 'albums.id', '=', 'base_albums.id')
				->select(['base_albums.id', 'title', 'parent_id']);

			// apply filter
			if ($userId != 0) {
				$shared_query = $shared_query->where('base_albums.owner_id', '=', $userId);
				$albums_query = $albums_query->where('owner_id', '=', $userId);
			}

			// get arrays
			$shared = $shared_query
				->orderBy('title', 'ASC')
				->orderBy('username', 'ASC')
				->get();

			$albums = $albums_query->get();
			$this->linkAlbums($albums);
			$albums->each(function ($album) {
				$album->title = $this->breadcrumbPath($album);
			});
			$albums->each(function ($album) {
				unset($album->parent_id);
				unset($album->parent);
			});

			$users = DB::table('users')
				->select(['id', 'username'])
				->where('id', '>', 0)
				->orderBy('username', 'ASC')
				->get()
				->each(function ($user) {
					$user->id = intval($user->id);
				});

			return new Shares($shared, $albums, $users);
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}
	}

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
			if (!$album->parent_id) {
				$album->parent = null;
			}
			$childAlbums = $groupedAlbums->get($album->id, []);
			foreach ($childAlbums as $childAlbum) {
				$childAlbum->parent = $album;
			}
		}
	}
}
