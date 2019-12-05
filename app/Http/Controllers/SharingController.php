<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Album;
use App\ModelFunctions\SessionFunctions;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SharingController extends Controller
{
	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	/**
	 * @param SessionFunctions $sessionFunctions
	 */
	public function __construct(SessionFunctions $sessionFunctions)
	{
		$this->sessionFunctions = $sessionFunctions;
	}

	/**
	 * Return the list of current sharing rights.
	 *
	 * @return array
	 */
	public function listSharing()
	{
		$UserId = $this->sessionFunctions->id();
		if ($UserId == 0) {
			$shared = DB::table('user_album')
				->select('user_album.id', 'user_id', 'album_id', 'username',
					'title')
				->join('users', 'user_id', 'users.id')
				->join('albums', 'album_id', 'albums.id')
				->orderBy('title', 'ASC')
				->orderBy('username', 'ASC')
				->get();

			$albums = Album::select(['id', 'title'])->orderBy('title', 'ASC')
				->get();
			$users = User::select(['id', 'username'])
				->orderBy('username', 'ASC')->get();
		} else {
			$shared = DB::table('user_album')
				->select('user_album.id', 'user_id', 'album_id', 'username',
					'title')
				->join('users', 'user_id', 'users.id')
				->join('albums', 'album_id', 'albums.id')
				->where('albums.owner_id', '=', $UserId)
				->orderBy('title', 'ASC')
				->orderBy('username', 'ASC')
				->get();

			$albums = Album::select(['id', 'title'])
				->where('owner_id', '=', $UserId)->orderBy('title', 'ASC')->get();
			$users = User::select(['id', 'username'])
				->orderBy('username', 'ASC')->get();
		}

		return [
			'shared' => $shared,
			'albums' => $albums,
			'users' => $users,
		];
	}

	/**
	 * FIXME: What does this function actually do ? It is not called anywhere in the Lychee-front O.o.
	 *
	 * @param Request $request
	 *
	 * @return array
	 */
	public function getUserList(Request $request)
	{
		$request->validate([
			'albumIDs' => 'string|required',
		]);
		$array_albumIDs = explode(',', $request['albumIDs']);
		sort($array_albumIDs);

		$users = User::select('id', 'username')->all();
		$shared = DB::table('user_album')
			->select('user_id', 'album_id')
			->whereIn('album_id', $array_albumIDs)
			->orderBy('user_id', 'ASC')
			->orderBy('album_id', 'ASC')
			->get();

		$user_share = [];
		foreach ($shared as $share) {
			if (!isset($user_share[$share['user_id']])) {
				$user_share[$share['user_id']] = [];
			}
			$user_share[$share['user_id']][] = $share['album_id'];
		}

		$return_array = [];
		foreach ($users as $user) {
			if (!isset($user_share[$user->id])) {
				$return_array[] = $user;
			} else {
				$no = false;

				// quick test to avoid the loop
				if (count($user_share[$user->id]) != count($array_albumIDs)) {
					$no = true;
				}

				$i = 0;
				while (!$no && $i < count($user_share[$user->id])) {
					if ($user_share[$user->id][$i] != $array_albumIDs[$i]) {
						$no = true;
					}
					$i++;
				}

				if ($no) {
					$return_array[] = $user;
				}
			}
		}

		return $return_array;
	}

	/**
	 * Add a sharing between selected users and selected albums.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function add(Request $request)
	{
		$request->validate([
			'UserIDs' => 'string|required',
			'albumIDs' => 'string|required',
		]);

		$users = User::whereIn('id', explode(',', $request['UserIDs']))->get();

		foreach ($users as $user) {
			$user->shared()->sync(explode(',', $request['albumIDs']), false);
		}

		return 'true';
	}

	/**
	 * Given a list of shared ID we delete them
	 * This function is the only reason why we test SharedIDs in
	 * app/Http/Middleware/UploadCheck.php.
	 *
	 * FIXME: make sure that the Lychee-front is sending the correct ShareIDs
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function delete(Request $request)
	{
		$request->validate([
			'ShareIDs' => 'string|required',
		]);

		DB::table('user_album')
			->whereIn('id', explode(',', $request['ShareIDs']))->delete();

		return 'true';
	}
}
