<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Middleware;

use App\Facades\AccessControl;
use App\Factories\AlbumFactory;
use App\Models\Album;
use App\Models\BaseModelAlbumImpl;
use App\Models\Logs;
use App\Models\Photo;
use Closure;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UploadCheck
{
	private AlbumFactory $albumFactory;

	public function __construct(AlbumFactory $albumFactory)
	{
		$this->albumFactory = $albumFactory;
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

		$ret = $this->album_check($request, $user->id);
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
	 * TODO: Migrate this to {@link \App\Actions\AlbumAuthorisationProvider}
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
			if ($this->albumFactory->isBuiltInSmartAlbum($albumIDs[$i]) || $albumIDs[$i] === '0') {
				array_splice($albumIDs, $i, 1);
			} else {
				$i++;
			}
		}

		// Since we count the result we need to ensure no duplicates.
		$albumIDs = array_unique($albumIDs);

		if (count($albumIDs) > 0) {
			$count = BaseModelAlbumImpl::query()->whereIn('id', $albumIDs)->where('owner_id', '=', $user_id)->count();
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
	 * TODO: Migrate this to {@link \App\Actions\PhotoAuthorisationProvider}
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
			$count = Photo::query()->whereIn('id', $photoIDs)->where('owner_id', '=', $user_id)->count();
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
		}
	}
}
