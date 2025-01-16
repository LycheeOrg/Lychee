<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\Internal\InvalidSmartIdException;
use App\Exceptions\UnauthorizedException;
use App\Factories\AlbumFactory;
use App\Models\Extensions\BaseAlbum;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller responsible for the Vue front end.
 */
class VueController extends Controller
{
	public const ACCESS = 'access';
	public const PASSWORD = 'password';

	/**
	 * @param string|null $albumId
	 * @param string|null $photoId
	 *
	 * @return View
	 *
	 * @throws ModelNotFoundException
	 * @throws InvalidSmartIdException
	 * @throws AuthorizationException
	 * @throws BindingResolutionException
	 */
	public function view(?string $albumId = null, ?string $photoId = null): View
	{
		$albumFactory = resolve(AlbumFactory::class);
		try {
			if ($albumId !== null) {
				$album = $albumFactory->findAbstractAlbumOrFail($albumId, false);

				session()->now('access', $this->check($album));
				session()->now('album', $album);
			}

			if ($photoId !== null) {
				$photo = Photo::findOrFail($photoId);
				Gate::authorize(PhotoPolicy::CAN_SEE, [Photo::class, $photo]);
				session()->now('photo', $photo);
			}
		} catch (ModelNotFoundException) {
			throw new NotFoundHttpException();
		}

		return view('vueapp');
	}

	/**
	 * Check if user can access the album.
	 *
	 * @param AbstractAlbum $album
	 *
	 * @return bool true if access, false if password required
	 *
	 * @throws UnauthorizedException if user is not authorized at all
	 */
	private function check(AbstractAlbum $album): bool
	{
		$result = Gate::check(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $album]);
		if (
			!$result &&
			$album instanceof BaseAlbum &&
			$album->public_permissions()?->password !== null
		) {
			return false;
		}

		return $result ? true : throw new UnauthorizedException();
	}
}
