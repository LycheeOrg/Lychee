<?php

namespace App\Http\Controllers;

use App\Actions\Search\AlbumSearch;
use App\Actions\Search\PhotoSearch;
use App\Contracts\Exceptions\LycheeException;
use App\Http\Requests\Search\SearchRequest;
use Illuminate\Routing\Controller;
use function Safe\json_encode;

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
	public function run(SearchRequest $request, AlbumSearch $albumSearch, PhotoSearch $photoSearch): array
	{
		$return = [];
		// For efficiency reasons, we directly call `toArray` here,
		// such that the conversion is not performed several times, e.g.
		// for `json_encode` below and at least a second time when the
		// result is sent to the client.
		$return['albums'] = $albumSearch->queryAlbums($request->terms())->toArray();
		$return['tag_albums'] = $albumSearch->queryTagAlbums($request->terms())->toArray();
		$return['photos'] = $photoSearch->query($request->terms())->toArray();
		// The checksum is used by the web front-end as an efficient way to
		// avoid rebuilding the GUI, if two subsequent searches return the
		// same result.
		// The front-end performs a live search, while the user is typing
		// a term.
		// If the GUI was rebuilt every time after the user had typed the
		// next character of a search term although the search result might
		// stay the same, the GUI would flicker.
		$return['checksum'] = md5(json_encode($return));

		return $return;
	}
}
