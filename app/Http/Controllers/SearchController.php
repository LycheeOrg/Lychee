<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Actions\Search\AlbumSearch;
use App\Actions\Search\PhotoSearch;
use App\Facades\AccessControl;
use App\Models\Configs;
use App\Response;
use Illuminate\Http\Request;

class SearchController extends Controller
{
	/**
	 * Escape special characters for a LIKE query.
	 *
	 * @param string $value
	 * @param string $char
	 *
	 * @return string
	 */
	private function escape_like(string $value, string $char = '\\'): string
	{
		return str_replace(
			[$char, '%', '_'],
			[$char . $char, $char . '%', $char . '_'],
			$value
		);
	}

	/**
	 * Given a string split it by spaces to get terms and make a like search on the database.
	 * We search on albums and photos. title, tags, description are considered.
	 * TODO: add search by date.
	 *
	 * @param Request $request
	 *
	 * @return array
	 */
	public function search(Request $request, AlbumSearch $albumSearch, PhotoSearch $photoSearch)
	{
		if (!AccessControl::is_logged_in() && Configs::get_value('public_search', '0') !== '1') {
			return Response::error('Search disabled.');
		}

		$request->validate(['term' => 'required|string']);

		$terms = explode(' ', $request['term']);

		$escaped_terms = [];
		foreach ($terms as $term) {
			$escaped_terms[] = $this->escape_like($term);
		}

		// Initialize return var
		$return = [];
		$return['albums'] = $albumSearch->query($escaped_terms);
		$return['photos'] = $photoSearch->query($escaped_terms);
		$return['hash'] = md5(json_encode($return));

		return $return;
	}
}
