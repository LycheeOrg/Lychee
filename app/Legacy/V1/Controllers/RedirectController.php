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

	public function __construct(Unlock $unlock, AlbumFactory $album_factory)
	{
		$this->unlock = $unlock;
		$this->albumFactory = $album_factory;
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
	public function album(Request $request, string $album_i_d): SymfonyResponse
	{
		return $this->photo($request, $album_i_d, null);
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
	public function photo(Request $request, string $album_i_d, ?string $photo_i_d): SymfonyResponse
	{
		try {
			if (Legacy::isLegacyModelID($album_i_d)) {
				$album_i_d = Legacy::translateLegacyAlbumID(intval($album_i_d), $request);
			}

			if ($photo_i_d !== null && Legacy::isLegacyModelID($photo_i_d)) {
				$photo_i_d = Legacy::translateLegacyPhotoID(intval($photo_i_d), $request);
			}

			if (
				$request->filled('password') &&
				Configs::getValueAsBool('unlock_password_photos_with_url_param')
			) {
				$album = $this->albumFactory->findBaseAlbumOrFail($album_i_d);
				$this->unlock->do($album, $request['password']);
			}

			// If we are using vuejs by default, we redirect to vuejs url intead.
			if (Features::active('vuejs')) {
				return $photo_i_d === null ?
					redirect(route('gallery-album', ['albumId' => $album_i_d])) :
					redirect(route('gallery-photo', ['albumId' => $album_i_d, 'photoId' => $photo_i_d]));
			}

			return $photo_i_d === null ?
				redirect('gallery#' . $album_i_d) :
				redirect('gallery#' . $album_i_d . '/' . $photo_i_d);
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
