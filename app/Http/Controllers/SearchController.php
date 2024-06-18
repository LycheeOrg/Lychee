<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Search\AlbumSearch;
use App\Actions\Search\PhotoSearch;
use App\Contracts\Exceptions\LycheeException;
use App\Http\Requests\Search\SearchRequest;
use App\Http\Resources\SearchResource;
use Illuminate\Routing\Controller;

class SearchController extends Controller
{
	/**
	 * Given a string split it by spaces to get terms and make a like search on the database.
	 * We search on albums and photos. title, tags, description are considered.
	 * TODO: add search by date.
	 *
	 * @param SearchRequest $request
	 * @param AlbumSearch   $albumSearch
	 * @param PhotoSearch   $photoSearch
	 *
	 * @return SearchResource
	 *
	 * @throws LycheeException
	 */
	public function run(SearchRequest $request, AlbumSearch $albumSearch, PhotoSearch $photoSearch): SearchResource
	{
		return new SearchResource(
			$albumSearch->queryAlbums($request->terms()),
			$albumSearch->queryTagAlbums($request->terms()),
			$photoSearch->query($request->terms())
		);
	}
}
