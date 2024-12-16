<?php

namespace App\Http\Controllers\Gallery;

use App\Actions\Albums\Top;
use App\Http\Resources\Collections\RootAlbumResource;
use App\Http\Resources\GalleryConfigs\RootConfig;
use Illuminate\Routing\Controller;

/**
 * Controller responsible for the config.
 */
class AlbumsController extends Controller
{
	/**
	 * Retrieve all the albums at the root.
	 *
	 * @return RootAlbumResource returns the top albums
	 */
	public function get(Top $top): RootAlbumResource
	{
		return RootAlbumResource::fromDTO($top->get(), new RootConfig());
	}
}