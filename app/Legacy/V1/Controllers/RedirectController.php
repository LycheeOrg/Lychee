<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Controllers;

use App\Actions\Album\Unlock;
use App\Assets\Features;
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
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class RedirectController extends Controller
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
	 *
	 * @codeCoverageIgnore Legacy stuff
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

			// If we are using vuejs by default, we redirect to vuejs url intead.
			if (Features::active('vuejs')) {
				return $photoID === null ?
					redirect(route('gallery-album', ['albumId' => $albumID])) :
					redirect(route('gallery-photo', ['albumId' => $albumID, 'photoId' => $photoID]));
			}

			return $photoID === null ?
				redirect('gallery#' . $albumID) :
				redirect('gallery#' . $albumID . '/' . $photoID);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Lychee redirection component', $e);
		}
	}

	/**
	 * Redirection to landing or gallery depending on the settings.
	 * Otherwise attach a JS hook if legacy is enabled.
	 *
	 * @return View|SymfonyResponse
	 *
	 * @codeCoverageIgnore Legacy stuff
	 */
	public function view(): View|SymfonyResponse
	{
		$base_route = Configs::getValueAsBool('landing_page_enable') ? route('landing') : route('gallery');
		if (Features::active('legacy_v4_redirect') === false) {
			return redirect($base_route);
		}

		return view('hook-redirection', [
			'gallery' => route('gallery'),
			'base' => $base_route,
		]);
	}
}
