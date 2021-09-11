<?php

namespace App\Http\Middleware;

use App\Actions\AlbumAuthorisationProvider;
use App\Actions\PhotoAuthorisationProvider;
use Closure;
use Illuminate\Http\Request;

class ReadCheck
{
	private AlbumAuthorisationProvider $albumAuthorisationProvider;
	private PhotoAuthorisationProvider $photoAuthorisationProvider;

	public function __construct(AlbumAuthorisationProvider $albumAuthorisationProvider, PhotoAuthorisationProvider $photoAuthorisationProvider)
	{
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
	public function handle($request, Closure $next)
	{
		$albumIDs = [];
		if ($request->has('albumIDs')) {
			$albumIDs = explode(',', $request['albumIDs']);
		}
		if ($request->has('albumID')) {
			$albumIDs[] = $request['albumID'];
		}
		foreach ($albumIDs as $albumID) {
			if (!$this->albumAuthorisationProvider->isAccessible($albumID)) {
				return response('', 403);
			}
		}

		$photoIDs = [];
		if ($request->has('photoIDs')) {
			$photoIDs = explode(',', $request['photoIDs']);
		}
		if ($request->has('photoID')) {
			$photoIDs[] = $request['photoID'];
		}
		foreach ($photoIDs as $photoID) {
			if (!$this->photoAuthorisationProvider->isVisible($photoID)) {
				return response('', 403);
			}
		}

		return $next($request); // access granted
	}
}
