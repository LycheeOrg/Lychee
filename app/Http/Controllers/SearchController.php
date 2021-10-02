<?php

namespace App\Http\Controllers;

use App\Actions\Search\AlbumSearch;
use App\Actions\Search\PhotoSearch;
use App\Contracts\LycheeException;
use App\Http\Requests\Search\SearchRequest;

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
	 * @return array
	 *
	 * @throws LycheeException
	 */
	public function search(SearchRequest $request, AlbumSearch $albumSearch, PhotoSearch $photoSearch): array
	{
		$return = [];
		$return['albums'] = $albumSearch->query($request->terms());
		$return['photos'] = $photoSearch->query($request->terms());

		return $return;
	}
}
