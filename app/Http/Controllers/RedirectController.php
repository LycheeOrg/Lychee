<?php

namespace App\Http\Controllers;

use App\Actions\Album\Unlock;
use App\Contracts\LycheeException;
use App\Exceptions\Internal\FrameworkException;
use App\Legacy\Legacy;
use App\Models\Configs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class RedirectController extends Controller
{
	/**
	 * @param Request $request
	 * @param string  $albumID
	 * @param Unlock  $unlock
	 *
	 * @return void
	 *
	 * @throws ModelNotFoundException
	 * @throws LycheeException
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
	 * @param Unlock  $unlock
	 *
	 * @return RedirectResponse
	 *
	 * @throws LycheeException
	 * @throws ModelNotFoundException
	 */
	public function album(Request $request, string $albumID, Unlock $unlock): SymfonyResponse
	{
		try {
			if (Legacy::isLegacyModelID($albumID)) {
				$albumID = Legacy::translateLegacyAlbumID($albumID, $request);
			}

			$this->passwordManagement($request, $albumID, $unlock);

			return redirect('gallery#' . $albumID);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Lychee redirection component', $e);
		}
	}

	/**
	 * Trivial redirection.
	 *
	 * @param Request $request
	 * @param string  $albumID
	 * @param string  $photoID
	 * @param Unlock  $unlock
	 *
	 * @return RedirectResponse
	 *
	 * @throws LycheeException
	 * @throws ModelNotFoundException
	 */
	public function photo(Request $request, string $albumID, string $photoID, Unlock $unlock): SymfonyResponse
	{
		try {
			if (Legacy::isLegacyModelID($albumID)) {
				$albumID = Legacy::translateLegacyAlbumID($albumID, $request);
			}

			if (Legacy::isLegacyModelID($photoID)) {
				$photoID = Legacy::translateLegacyPhotoID($photoID, $request);
			}

			$this->passwordManagement($request, $albumID, $unlock);

			return redirect('gallery#' . $albumID . '/' . $photoID);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Lychee redirection component', $e);
		}
	}
}
