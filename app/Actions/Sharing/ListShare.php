<?php

namespace App\Actions\Sharing;

use App\Models\Album;
use App\Models\User;
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
				'base_album_id',
				'username',
				'title',
			])
			->join('users', 'user_id', 'users.id')
			->join('base_albums', 'base_album_id', 'base_albums.id');

		$albums_query = Album::select(['id', 'title', 'parent_id']);

		// apply filter
		if ($UserId != 0) {
			$shared_query = $shared_query->where('base_albums.owner_id', '=', $UserId);
			$albums_query = $albums_query->where('owner_id', '=', $UserId);
		}

		// get arrays
		$shared = $shared_query->orderBy('title', 'ASC')
			->orderBy('username', 'ASC')
			->get()
			->each(function ($s) {
				$s->album_id = strval($s->album_id);
				$s->title = Album::getFullPath($s);
			});

		$albums = $albums_query->get()->each(function ($album) {
			$album->title = Album::getFullPath($album);
		});

		$users = User::select(['id', 'username'])
			->where('id', '>', 0)
			->orderBy('username', 'ASC')->get();

		return [
			'shared' => $shared,
			'albums' => $albums,
			'users' => $users,
		];
	}
}
