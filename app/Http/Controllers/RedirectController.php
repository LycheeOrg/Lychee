<?php

namespace App\Http\Controllers;

use App\Actions\Album\Unlock;
use App\Contracts\Exceptions\LycheeException;
use App\Exceptions\Internal\FrameworkException;
use App\Factories\AlbumFactory;
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
	protected Unlock $unlock;
	protected AlbumFactory $albumFactory;

	public function __construct(Unlock $unlock, AlbumFactory $albumFactory)
	{
		$this->unlock = $unlock;
		$this->albumFactory = $albumFactory;
	}

	/**
	 * Trivial redirection.
	 *
	 * @param Request $request
	 * @param string  $albumID
	 *
	 * @return RedirectResponse
	 *
	 * @throws LycheeException
	 * @throws ModelNotFoundException
	 */
	public function album(Request $request, string $albumID): SymfonyResponse
	{
		return $this->photo($request, $albumID, null);
	}

	/**
	 * Trivial redirection.
	 *
	 * @param Request     $request
	 * @param string      $albumID
	 * @param string|null $photoID
	 *
	 * @return RedirectResponse
	 *
	 * @throws LycheeException
	 * @throws ModelNotFoundException
	 */
	public function photo(Request $request, string $albumID, ?string $photoID): SymfonyResponse
	{
		try {
			if (Legacy::isLegacyModelID($albumID)) {
				$albumID = Legacy::translateLegacyAlbumID(intval($albumID), $request);
			}

			if ($photoID !== null && Legacy::isLegacyModelID($photoID)) {
				$photoID = Legacy::translateLegacyPhotoID(intval($photoID), $request);
			}

			if (
				$request->filled('password') &&
				Configs::getValueAsBool('unlock_password_photos_with_url_param')
			) {
				$album = $this->albumFactory->findBaseAlbumOrFail($albumID);
				$this->unlock->do($album, $request['password']);
			}

			return $photoID === null ?
				redirect('gallery#' . $albumID) :
				redirect('gallery#' . $albumID . '/' . $photoID);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Lychee redirection component', $e);
		}
	}
}
