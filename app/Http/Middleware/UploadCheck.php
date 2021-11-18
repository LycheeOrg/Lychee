<?php

namespace App\Http\Middleware;

use App\Actions\AlbumAuthorisationProvider;
use App\Actions\PhotoAuthorisationProvider;
use App\Facades\AccessControl;
use App\Models\Album;
use App\Models\Logs;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class UploadCheck
{
	private AlbumAuthorisationProvider $albumAuthorisationProvider;
	private PhotoAuthorisationProvider $photoAuthorisationProvider;

	public function __construct(
		AlbumAuthorisationProvider $albumAuthorisationProvider,
		PhotoAuthorisationProvider $photoAuthorisationProvider
	) {
		$this->albumAuthorisationProvider = $albumAuthorisationProvider;
		$this->photoAuthorisationProvider = $photoAuthorisationProvider;
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
		if (!AccessControl::is_logged_in()) {
			return response('', 401);
		}

		// is admin
		if (AccessControl::is_admin()) {
			return $next($request);
		}

		$user = AccessControl::user();

		// is not admin and does not have upload rights
		if (!$user->upload) {
			return response('', 403);
		}

		$ret = $this->album_check($request);
		if ($ret === false) {
			return response('', 403);
		}

		$ret = $this->photo_check($request, $user->id);
		if ($ret === false) {
			return response('', 403);
		}

		// Only used for /api/Sharing::Delete
		$ret = $this->share_check($request, $user->id);
		if ($ret === false) {
			return response('', 403);
		}

		return $next($request);
	}

	/**
	 * Take of checking if a user can actually modify that Album.
	 *
	 * @param Request $request
	 *
	 * @return bool
	 */
	private function album_check(Request $request): bool
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

		return $this->albumAuthorisationProvider->areEditable($albumIDs);
	}

	/**
	 * Check if the user is authorized to do anything to that picture.
	 *
	 * @param Request $request
	 *
	 * @return bool
	 */
	private function photo_check(Request $request): bool
	{
		$photoIDs = [];
		if ($request->has('photoIDs')) {
			$photoIDs = explode(',', $request['photoIDs']);
		}
		if ($request->has('photoID')) {
			$photoIDs[] = $request['photoID'];
		}

		return $this->photoAuthorisationProvider->areEditable($photoIDs);
	}

	/**
	 * @param Request $request
	 * @param int     $user_id
	 *
	 * @return bool
	 */
	private function share_check(Request $request, int $user_id): bool
	{
		if ($request->has('ShareIDs')) {
			$shareIDs = $request['ShareIDs'];

			$albums = Album::query()->whereIn('id', function (Builder $query) use ($shareIDs) {
				$query->select('album_id')
					->from('user_base_album')
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
		} else {
			return true;
		}
	}
}
