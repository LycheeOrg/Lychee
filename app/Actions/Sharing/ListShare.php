<?php

namespace App\Actions\Sharing;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ListShare
{
	public function do(int $UserId): array
	{
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
		if ($UserId != 0) {
			$shared_query = $shared_query->where('base_albums.owner_id', '=', $UserId);
			$albums_query = $albums_query->where('owner_id', '=', $UserId);
		}

		// get arrays
		$shared = $shared_query
			->orderBy('title', 'ASC')
			->orderBy('username', 'ASC')
			->get()
			->each(function ($share) {
				$share->album_id = intval($share->album_id);
			});

		$albums = $albums_query->get();
		$this->linkAlbums($albums);
		$albums->each(function ($album) {
			$album->title = $this->breadcrumbPath($album);
		});
		$albums->each(function ($album) {
			$album->id = intval($album->id);
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

		return [
			'shared' => $shared,
			'albums' => $albums,
			'users' => $users,
		];
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
