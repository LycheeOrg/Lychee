<?php

namespace App\Http\Controllers;

use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\Internal\InvalidSmartIdException;
use App\Factories\AlbumFactory;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
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
				Gate::authorize(AlbumPolicy::CAN_ACCESS, [AbstractAlbum::class, $album]);
				session()->now('album', $album);
			}

			if ($photoId !== null) {
				$photo = Photo::findOrFail($photoId);
				Gate::authorize(\PhotoPolicy::CAN_SEE, [Photo::class, $photo]);
				session()->now('photo', $photo);
			}
		} catch (ModelNotFoundException) {
			throw new NotFoundHttpException();
		}

		return view('vueapp');
	}
}