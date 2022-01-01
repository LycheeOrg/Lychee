<?php

namespace App\Http\Controllers;

use App\Actions\Album\Unlock;
use App\Legacy\Legacy;
use App\Models\Configs;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class RedirectController extends Controller
{
	/**
	 * @param Request $request
	 * @param string  $albumID
	 * @param Unlock  $unlock
	 *
	 * @return void
	 */
	private function passwordManagement(Request $request, string $albumID, Unlock $unlock): void
	{
		if ($request->filled('password')) {
			if (Configs::get_value('unlock_password_photos_with_url_param', '0') == '1') {
				$unlock->propagate($request['password']);
			} else {
				$unlock->do($albumID, $request['password']);
			}
		}
	}

	/**
	 * Trivial redirection.
	 *
	 * @param Request $request
	 * @param string  $albumID
	 */
	public function album(Request $request, string $albumID, Unlock $unlock): SymfonyResponse
	{
		if (Legacy::isLegacyModelID($albumID)) {
			$albumID = Legacy::translateLegacyAlbumID($albumID, $request);
			if ($albumID === null) {
				abort(SymfonyResponse::HTTP_NOT_FOUND);
			}
		}

		$this->passwordManagement($request, $albumID, $unlock);

		return redirect('gallery#' . $albumID);
	}

	/**
	 * Trivial redirection.
	 *
	 * @param Request $request
	 * @param string  $albumID
	 * @param string  $photoID
	 */
	public function photo(Request $request, string $albumID, string $photoID, Unlock $unlock): SymfonyResponse
	{
		if (Legacy::isLegacyModelID($albumID)) {
			$albumID = Legacy::translateLegacyAlbumID($albumID, $request);
			if ($albumID === null) {
				abort(SymfonyResponse::HTTP_NOT_FOUND);
			}
		}

		if (Legacy::isLegacyModelID($photoID)) {
			$photoID = Legacy::translateLegacyPhotoID($photoID, $request);
			if ($photoID === null) {
				abort(SymfonyResponse::HTTP_NOT_FOUND);
			}
		}

		$this->passwordManagement($request, $albumID, $unlock);

		return redirect('gallery#' . $albumID . '/' . $photoID);
	}
}
