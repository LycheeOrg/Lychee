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
	public function __construct(
		private Unlock $unlock,
		private AlbumFactory $album_factory)
	{
	}

	/**
	 * Trivial redirection.
	 *
	 * @return RedirectResponse
	 *
	 * @throws LycheeException
	 * @throws ModelNotFoundException
	 *
	 * @codeCoverageIgnore Legacy stuff
	 */
	public function redirect(Request $request, string $album_id, ?string $photo_id = null): SymfonyResponse
	{
		try {
			if (Legacy::isLegacyModelID($album_id)) {
				$album_id = Legacy::translateLegacyAlbumID(intval($album_id), $request);
			}

			if ($photo_id !== null && Legacy::isLegacyModelID($photo_id)) {
				$photo_id = Legacy::translateLegacyPhotoID(intval($photo_id), $request);
			}

			if (
				$request->filled('password') &&
				Configs::getValueAsBool('unlock_password_photos_with_url_param')
			) {
				$album = $this->album_factory->findBaseAlbumOrFail($album_id);
				$this->unlock->do($album, $request['password']);
			}

			// If we are using vuejs by default, we redirect to vuejs url intead.
			if (Features::active('vuejs')) {
				return $photo_id === null ?
					redirect(route('gallery', ['albumId' => $album_id])) :
					redirect(route('gallery', ['albumId' => $album_id, 'photoId' => $photo_id]));
			}

			return $photo_id === null ?
				redirect('gallery#' . $album_id) :
				redirect('gallery#' . $album_id . '/' . $photo_id);
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