<?php

namespace App\Http\Middleware;

use App\Actions\AlbumAuthorisationProvider;
use App\Actions\ReadAccessFunctions;
use App\Models\Photo;
use Closure;
use Illuminate\Http\Request;

class ReadCheck
{
	private AlbumAuthorisationProvider $albumAuthorisationProvider;
	private ReadAccessFunctions $readAccessFunctions;

	public function __construct(AlbumAuthorisationProvider $albumAuthorisationProvider, ReadAccessFunctions $readAccessFunctions)
	{
		$this->albumAuthorisationProvider = $albumAuthorisationProvider;
		$this->readAccessFunctions = $readAccessFunctions;
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
				return response('false', 403);
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
			/** @var Photo $photo */
			$photo = Photo::with('album')->findOrFail($photoID);
			if ($this->readAccessFunctions->photo($photo) === false) {
				return response('', 403);
			}
		}

		return $next($request); // access granted
	}
}
