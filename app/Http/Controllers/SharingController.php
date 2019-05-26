<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Album;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SharingController extends Controller
{
    public function listSharing()
    {
        if (Session::get('UserID') == 0) {
            $shared = DB::table('user_album')
				->select('user_album.id', 'user_id', 'album_id', 'username', 'title')
				->join('users', 'user_id', 'users.id')
				->join('albums', 'album_id', 'albums.id')
				->orderBy('title', 'ASC')
				->orderBy('username', 'ASC')
				->get();

            $albums = Album::select(['id', 'title'])->orderBy('title', 'ASC')->get();
            $users = User::select(['id', 'username'])->orderBy('username', 'ASC')->get();
        } else {
            $id = Session::get('UserID');
            $shared = DB::table('user_album')
				->select('user_album.id', 'user_id', 'album_id', 'username', 'title')
				->join('users', 'user_id', 'users.id')
				->join('albums', 'album_id', 'albums.id')
				->where('albums.owner_id', '=', $id)
				->orderBy('title', 'ASC')
				->orderBy('username', 'ASC')
				->get();

            $albums = Album::select(['id', 'title'])->where('owner_id', '=', $id)->orderBy('title', 'ASC')->get();
            $users = User::select(['id', 'username'])->orderBy('username', 'ASC')->get();
        }

        return [
			'shared' => $shared,
			'albums' => $albums,
			'users' => $users,
		];
    }

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

        $user_share = array();
        foreach ($shared as $share) {
            if (!isset($user_share[$share['user_id']])) {
                $user_share[$share['user_id']] = array();
            }
            $user_share[$share['user_id']][] = $share['album_id'];
        }

        $return_array = array();
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
                    ++$i;
                }

                if ($no) {
                    $return_array[] = $user;
                }
            }
        }

        return $return_array;
    }

    /**
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

    public function delete(Request $request)
    {
        $request->validate([
			'ShareIDs' => 'string|required',
		]);

        DB::table('user_album')->whereIn('id', explode(',', $request['ShareIDs']))->delete();

        return 'true';
    }
}
