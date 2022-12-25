<?php

namespace App\Http\Controllers;

use App\Actions\Albums\Top;
use App\Contracts\Exceptions\LycheeException;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use function Safe\json_encode;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class DemoController extends Controller
{
	/**
	 * This function returns what are the possible return output to simulate
	 * the server interaction in the case of the demo server here:
	 * https://lycheeorg.github.io/demo/.
	 *
	 * Call /demo and use the generated code to replace the api.post() function
	 *
	 * @return Response|RedirectResponse
	 *
	 * @throws LycheeException
	 * @throws BindingResolutionException
	 * @throws RouteNotFoundException
	 * @throws ModelNotFoundException
	 */
	public function js()
	{
		if (!Configs::getValueAsBool('gen_demo_js')) {
			return redirect()->route('home');
		}

		$functions = [];

		/**
		 * Session::init.
		 */
		$session_init = resolve(SessionController::class);
		$return_session = [];
		$return_session['name'] = 'Session::init()';
		$return_session['type'] = 'string';
		$return_session['data'] = json_encode($session_init->init());

		$functions[] = $return_session;

		/**
		 * Albums::get.
		 */
		$albums_controller = resolve(AlbumsController::class);
		$top = resolve(Top::class);

		$return_albums = [];
		$return_albums['name'] = 'Albums::get';
		$return_albums['type'] = 'string';
		$return_albums['data'] = json_encode($albums_controller->get($top));

		$functions[] = $return_albums;

		/**
		 * Album::get.
		 */
		$return_album_list = [];
		$return_album_list['name'] = 'Album::get';
		$return_album_list['type'] = 'array';
		$return_album_list['kind'] = 'albumID';
		$return_album_list['array'] = [];

		/** @var Collection<Album> $albums */
		$albums = Album::query()
			->select(['albums.*'])
			->with(['photos', 'photos.size_variants', 'photos.size_variants.sym_links'])
			->join('base_albums', 'base_albums.id', '=', 'albums.id')
			->where('base_albums.is_public', '=', true)
			->where('base_albums.is_link_required', '=', false)
			->get();
		/** @var Album $album */
		foreach ($albums as $album) {
			/**
			 * Copied and pasted from Album::get().
			 */
			$return_album = [];
			$return_album['id'] = $album->id;
			$return_album['data'] = json_encode($album->toArray());
			$return_album_list['array'][] = $return_album;
		}

		$functions[] = $return_album_list;

		/**
		 * Photo::get.
		 */
		$return_photo_list = [];
		$return_photo_list['name'] = 'Photo::get';
		$return_photo_list['type'] = 'array';
		$return_photo_list['kind'] = 'photoID';
		$return_photo_list['array'] = [];

		foreach ($albums as $album) {
			/** @var Photo $photo */
			foreach ($album->photos as $photo) {
				$return_photo = [];
				$return_photo_json = $photo->toArray();
				$return_photo_json['original_album'] = $return_photo_json['album_id'];
				$return_photo_json['album'] = $album->id;
				$return_photo['id'] = $photo->id;
				$return_photo['data'] = json_encode($return_photo_json);

				$return_photo_list['array'][] = $return_photo;
			}
		}

		$functions[] = $return_photo_list;

		$contents = view('demo', ['functions' => $functions]);
		$response = \response($contents, 200);
		$response->header('Content-Type', 'text/plain');

		return $response;
	}
}
