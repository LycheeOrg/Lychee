<?php

namespace App\Http\Controllers;

use App\Album;
use App\Photo;
use function foo\func;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

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
	private static function escape_like(string $value, string $char = '\\'): string
	{
		return str_replace(
			[$char, '%', '_'],
			[$char.$char, $char.'%', $char.'_'],
			$value
		);
	}

	public function search(Request $request)
	{

		$request->validate([
			'term' => 'required|string'
		]);

		// Initialize return var
		$return = array(
			'photos' => null,
			'albums' => null,
			'hash'   => ''
		);

		$terms= explode(' ', $request['term']);

		$escaped_terms = array();

		foreach ($terms as $term)
		{
			$escaped_terms[] = SearchController::escape_like($term);
		}

		$id = Session::get('UserID');

		/**
		 * Photos
		 */
		// for now we only look in OUR pictures
		$query = Photo::where('owner_id','=',$id);
		for ($i = 0; $i < count($escaped_terms); ++$i)
		{
			$escaped_term = $escaped_terms[$i];
			$query = $query->Where(
				function ($query) use ($id, $escaped_term) {
					$query->where('title','like','%'.$escaped_term.'%')
						->orWhere('description','like','%'.$escaped_term.'%')
						->orWhere('tags','like','%'.$escaped_term.'%');
				});
		}
		$photos = $query->get();

		if ($photos != null)
		{
			$i = 0;
			foreach($photos as $photo) {
				$return['photos'][$i] = $photo->prepareData();
				++$i;
			}
		}

		/**
		 * Albums
		 */
		$query = Album::where('owner_id','=',$id);
		for ($i = 0; $i < count($escaped_terms); ++$i)
		{
			$escaped_term = $escaped_terms[$i];
			$query = $query->Where(
				function ($query) use ($id, $escaped_term) {
					$query->where('title','like','%'.$escaped_term.'%')
						->orWhere('description','like','%'.$escaped_term.'%');
				});
		}
		$albums = $query->get();
		if ($albums != null)
		{
			$i = 0;
			foreach ($albums as $album_model) {
				$album = $album_model->prepareData();
				$album['sysstamp'] = $album_model['created_at'];
				$album = $album_model->gen_thumbs($album);
				$return['albums'][$i] = $album;
				++$i;
			}
		}

		// Hash
		$return['hash'] = md5(json_encode($return));
		return $return;

	}

}