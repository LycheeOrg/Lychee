<?php

namespace App\Http\Middleware;

use App\Actions\AlbumAuthorisationProvider;
use App\Actions\PhotoAuthorisationProvider;
use App\Contracts\InternalLycheeException;
use App\Exceptions\UnauthorizedException;
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
	 *
	 * @throws InternalLycheeException
	 * @throws UnauthorizedException
	 */
	public function handle(Request $request, Closure $next): mixed
	{
		$albumIDs = [];
		if ($request->has('albumIDs')) {
			$albumIDs = explode(',', $request['albumIDs']);
		}
		if ($request->has('albumID')) {
			$albumIDs[] = $request['albumID'];
		}
		foreach ($albumIDs as $albumID) {
			if (!$this->albumAuthorisationProvider->isAccessibleByID($albumID)) {
				throw new UnauthorizedException();
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
				throw new UnauthorizedException();
			}
		}

		return $next($request); // access granted
	}
}
