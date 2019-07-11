<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Middleware;

use App\Album;
use App\Logs;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\SessionFunctions;
use App\Photo;
use App\User;
use Closure;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UploadCheck
{
	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	/**
	 * @var AlbumFunctions
	 */
	private $albumFunctions;

	public function __construct(SessionFunctions $sessionFunctions, AlbumFunctions $albumFunctions)
	{
		$this->sessionFunctions = $sessionFunctions;
		$this->albumFunctions = $albumFunctions;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param Request $request
	 * @param Closure $next
	 *
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next)
	{
		// not logged!
		if (!$this->sessionFunctions->is_logged_in()) {
			return response('false');
		}

		// is admin
		if ($this->sessionFunctions->is_admin()) {
			return $next($request);
		}

		$user_id = $this->sessionFunctions->id();
		$user = User::find($user_id);
		if ($user == null) {
			return response('false');
		}

		// is not admin and does not have upload rights
		if (!$user->upload) {
			return response('false');
		}

		$ret = $this->album_check($request, $user_id);
		if ($ret === false) {
			return response('false');
		}

		$ret = $this->photo_check($request, $user_id);
		if ($ret === false) {
			return response('false');
		}

		// Only used for /api/Sharing::Delete
		$ret = $this->share_check($request, $user_id);
		if ($ret === false) {
			return response('false');
		}

		return $next($request);
	}

	/**
	 * Take of checking if a user can actually modify that Album.
	 *
	 * @param $request
	 * @param int $user_id
	 *
	 * @return ResponseFactory|Response|mixed
	 */
	public function album_check(Request $request, int $user_id)
	{
		$albumIDs = [];
		if ($request->has('albumIDs')) {
			$albumIDs = explode(',', $request['albumIDs']);
		}
		if ($request->has('albumID')) {
			$albumIDs[] = $request['albumID'];
		}
		if ($request->has('parent_id')) {
			$albumIDs[] = $request['parent_id'];
		}

		// Remove smart albums (they get a pass).
		for ($i = 0; $i < count($albumIDs);) {
			if ($this->albumFunctions->is_smart_album($albumIDs[$i])) {
				array_splice($albumIDs, $i, 1);
			} else {
				$i++;
			}
		}

		// Since we count the result we need to ensure no duplicates.
		$albumIDs = array_unique($albumIDs);

		if (count($albumIDs) > 0) {
			$count = Album::whereIn('id', $albumIDs)->where('owner_id', '=', $user_id)->count();
			if ($count !== count($albumIDs)) {
				Logs::error(__METHOD__, __LINE__, 'Albums not found or ownership mismatch!');

				return false;
			}
		}

		return true;
	}

	/**
	 * Check if the user is authorized to do anything to that picture.
	 *
	 * @param Request $request
	 * @param int     $user_id
	 *
	 * @return ResponseFactory|Response|mixed
	 */
	public function photo_check(Request $request, int $user_id)
	{
		$photoIDs = [];
		if ($request->has('photoIDs')) {
			$photoIDs = explode(',', $request['photoIDs']);
		}
		if ($request->has('photoID')) {
			$photoIDs[] = $request['photoID'];
		}

		// Since we count the result we need to ensure no duplicates.
		$photoIDs = array_unique($photoIDs);

		if (count($photoIDs) > 0) {
			$count = Photo::whereIn('id', $photoIDs)->where('owner_id', '=', $user_id)->count();
			if ($count !== count($photoIDs)) {
				Logs::error(__METHOD__, __LINE__, 'Photos not found or ownership mismatch!');

				return false;
			}
		}

		return true;
	}

	/**
	 * @param Request $request
	 * @param int     $user_id
	 *
	 * @return bool
	 */
	public function share_check(Request $request, int $user_id)
	{
		if ($request->has('ShareIDs')) {
			$shareIDs = $request['ShareIDs'];

			$albums = Album::whereIn('id', function (Builder $query) use ($shareIDs) {
				$query->select('album_id')
					->from('user_album')
					->whereIn('id', explode(',', $shareIDs));
			})->select('owner_id')->get();

			if ($albums == null) {
				Logs::error(__METHOD__, __LINE__, 'Could not find specified albums');

				return false;
			}
			$no_error = true;
			foreach ($albums as $album_t) {
				$no_error &= ($album_t->owner_id == $user_id);
			}
			if ($no_error) {
				return true;
			}

			Logs::error(__METHOD__, __LINE__, 'Album ownership mismatch!');

			return false;
		}
	}
}
