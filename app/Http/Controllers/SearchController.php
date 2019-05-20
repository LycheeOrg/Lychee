<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Configs;
use App\Album;
use App\ModelFunctions\AlbumFunctions;
use App\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SearchController extends Controller
{
	/**
	 * @var AlbumFunctions
	 */
	private $albumFunctions;

	/**
	 * @param AlbumFunctions $albumFunctions
	 */
	public function __construct(AlbumFunctions $albumFunctions)
	{
		$this->albumFunctions = $albumFunctions;
	}

	/**
	 * Escape special characters for a LIKE query.
	 *
	 * @param string $value
	 * @param string $char
	 *
	 * @return string
	 */
	private static function escape_like(string $value, string $char = '\\'): string
	{
		return str_replace(
			[
				$char,
				'%',
				'_',
			],
			[
				$char.$char,
				$char.'%',
				$char.'_',
			],
			$value
		);
	}

	public function search(Request $request)
	{
		$request->validate([
			'term' => 'required|string',
		]);

		// Initialize return var
		$return = array(
			'photos' => null,
			'albums' => null,
			'hash' => '',
		);

		$terms = explode(' ', $request['term']);

		$escaped_terms = array();

		foreach ($terms as $term) {
			$escaped_terms[] = SearchController::escape_like($term);
		}

		$id = Session::get('UserID');

		/**
		 * Photos.
		 */
		// for now we only look in OUR pictures
		$query = Photo::where('id', '>', 0);
		$query->where(function (Builder $query) use ($id) {
			$query->where('owner_id', '=', $id);
			if (Configs::get_value('public_search', '0') == '1') {
				$query = $query->orWhere('public', '>=', 0);
			}		
		});
		for ($i = 0; $i < count($escaped_terms); ++$i) {
			$escaped_term = $escaped_terms[$i];
			$query = $query->Where(
				function (Builder $query) use ($id, $escaped_term) {
					$query->where('title', 'like', '%'.$escaped_term.'%')
						->orWhere('description', 'like', '%'.$escaped_term.'%')
						->orWhere('tags', 'like', '%'.$escaped_term.'%');
				});
		}
		$photos = $query->get();

		if ($photos != null) {
			$i = 0;
			foreach ($photos as $photo) {
				if (Session::has('UserID') || $photo->get_public()) {
					$return['photos'][$i] = $photo->prepareData();
					++$i;
				}
			}
		}

		/**
		 * Albums.
		 */
		$query = Album::where('id', '>', 0);
		$query->where(function (Builder $query) use ($id) {
			$query->where('owner_id', '=', $id);
			if (Configs::get_value('public_search', '0') == '1') {
				$query = $query->orWhere('public', '=', 1);
			}		
		});		
		for ($i = 0; $i < count($escaped_terms); ++$i) {
			$escaped_term = $escaped_terms[$i];
			$query = $query->Where(
				function (Builder $query) use ($id, $escaped_term) {
					$query->where('title', 'like', '%'.$escaped_term.'%')
						->orWhere('description', 'like', '%'.$escaped_term.'%');
				});
		}
		$albums = $query->get();
		if ($albums != null) {
			$i = 0;
			foreach ($albums as $album_model) {
				$album = $album_model->prepareData();
				$album = $album_model->gen_thumbs($album, $this->albumFunctions->get_sub_albums($album_model, [$album_model->id]));
				$return['albums'][$i] = $album;
				$i++;
			}
		}

		// Hash
		$return['hash'] = md5(json_encode($return));

		return $return;
	}
}
