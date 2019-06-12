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
		if ($request->has('albumID') || $request->has('parent_id')) {
			$albumID = $request->has('albumID') ? $request['albumID'] : $request['parent_id'];

			if ($this->albumFunctions->is_smart_album($albumID)) {
				return true;
			}

			$num = Album::where('id', '=', $albumID)->where('owner_id', '=', $user_id)->count();
			if ($num == 0) {
				Logs::error(__METHOD__, __LINE__, 'Could not find specified album');

				return false;
			}

			return true;
		}

		if ($request->has('albumIDs')) {
			$albumIDs = $request['albumIDs'];

			$albums = Album::whereIn('id', explode(',', $albumIDs))->get();
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

		return null;
	}

	/**
	 * Check if the user is authorized to do anything to that picture.
	 *
	 * @param Request $request
	 * @param int     $id
	 *
	 * @return ResponseFactory|Response|mixed
	 */
	public function photo_check(Request $request, int $id)
	{
		if ($request->has('photoID')) {
			$photoID = $request['photoID'];
			$num = Photo::where('id', '=', $photoID)->where('owner_id', '=', $id)->count();
			if ($num == 0) {
				Logs::error(__METHOD__, __LINE__, 'Could not find specified photo');

				return false;
			}

			return true;
		}

		if ($request->has('photoIDs')) {
			$photoIDs = $request['photoIDs'];

			$photos = Photo::whereIn('id', explode(',', $photoIDs))->get();
			if ($photos == null) {
				Logs::error(__METHOD__, __LINE__, 'Could not find specified photos');

				return false;
			}
			$no_error = true;
			foreach ($photos as $photo_t) {
				// either you own the picture or it is in an album you own
				$no_error &= (($photo_t->owner_id == $id) || ($photo_t->album != null && $photo_t->album->owner_id == $id));
			}
			if ($no_error) {
				return true;
			}

			Logs::error(__METHOD__, __LINE__, 'Photos ownership mismatch!');

			return false;
		}
	}

	/**
	 * @param Request $request
	 * @param int     $id
	 *
	 * @return bool
	 */
	public function share_check(Request $request, int $id)
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
				$no_error &= ($album_t->owner_id == $id);
			}
			if ($no_error) {
				return true;
			}

			Logs::error(__METHOD__, __LINE__, 'Album ownership mismatch!');

			return false;
		}
	}
}
