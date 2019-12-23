<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Album;
use App\Configs;
use App\ControllerFunctions\ReadAccessFunctions;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\SessionFunctions;
use App\ModelFunctions\SymLinkFunctions;
use App\Photo;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SearchController extends Controller
{
	/**
	 * @var AlbumFunctions
	 */
	private $albumFunctions;

	/**
	 * @var SessionFunctions
	 */
	private $sessionFunctions;

	/**
	 * @var readAccessFunctions
	 */
	private $readAccessFunctions;

	/**
	 * @var SymLinkFunctions
	 */
	private $symLinkFunctions;

	/**
	 * @param AlbumFunctions      $albumFunctions
	 * @param SessionFunctions    $sessionFunctions
	 * @param ReadAccessFunctions $readAccessFunctions
	 * @param SymLinkFunctions    $symLinkFunctions
	 */
	public function __construct(AlbumFunctions $albumFunctions, SessionFunctions $sessionFunctions, ReadAccessFunctions $readAccessFunctions, SymLinkFunctions $symLinkFunctions)
	{
		$this->albumFunctions = $albumFunctions;
		$this->sessionFunctions = $sessionFunctions;
		$this->readAccessFunctions = $readAccessFunctions;
		$this->symLinkFunctions = $symLinkFunctions;
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
				$char . $char,
				$char . '%',
				$char . '_',
			],
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
	public function search(Request $request)
	{
		if (!$this->sessionFunctions->is_logged_in() && Configs::get_value('public_search', '0') !== '1') {
			return Response::error('Search disabled.');
		}

		$request->validate([
			'term' => 'required|string',
		]);

		// Initialize return var
		$return = [
			'photos' => null,
			'albums' => null,
			'hash' => '',
		];

		$terms = explode(' ', $request['term']);

		$escaped_terms = [];

		foreach ($terms as $term) {
			$escaped_terms[] = SearchController::escape_like($term);
		}

		/**
		 * Albums.
		 *
		 * Begin by building a list of all albums and subalbums accessible
		 * from the top level.  This includes password-protected albums
		 * (since they are visible) but not their content.
		 */
		$toplevel = $this->albumFunctions->getToplevelAlbums();
		if ($toplevel === null) {
			return Response::error('I could not find you.');
		}

		$albumIDs = [];
		if ($toplevel['albums'] !== null) {
			foreach ($toplevel['albums'] as $album) {
				$albumIDs[] = $album->id;
				if ($this->readAccessFunctions->album($album) === 1) {
					$this->albumFunctions->get_sub_albums($albumIDs, $album, true);
				}
			}
		}
		if ($toplevel['shared_albums'] !== null) {
			foreach ($toplevel['shared_albums'] as $album) {
				$albumIDs[] = $album->id;
				if ($this->readAccessFunctions->album($album) === 1) {
					$this->albumFunctions->get_sub_albums($albumIDs, $album, true);
				}
			}
		}

		$query = Album::with([
			'owner',
			'children',
		])
			->whereIn('id', $albumIDs);
		for ($i = 0; $i < count($escaped_terms); $i++) {
			$escaped_term = $escaped_terms[$i];
			$query->where(
				function (Builder $query) use ($escaped_term) {
					$query->where('title', 'like', '%' . $escaped_term . '%')
						->orWhere('description', 'like', '%' . $escaped_term . '%');
				});
		}
		$albums = $query->get();
		if ($albums != null) {
			$i = 0;
			foreach ($albums as $album_model) {
				$album = $album_model->prepareData();
				if ($this->sessionFunctions->is_logged_in()) {
					$album['owner'] = $album_model->owner->username;
				}
				if ($this->readAccessFunctions->album($album_model) === 1) {
					// We don't need 'albums' but we do need to come up with
					// all the subalbums in order to get accurate thumbs info
					// and to let the front end know if there are any.
					$subAlbums = [$album_model->id];
					$this->albumFunctions->get_sub_albums($subAlbums, $album_model);
					$this->albumFunctions->gen_thumbs($album, $subAlbums);
					$album['has_albums'] = count($subAlbums) > 1 ? '1' : '0';
				}
				unset($album['thumbIDs']);
				$return['albums'][$i] = $album;
				$i++;
			}
		}

		/*
		 * Photos.
		 *
		 * Begin by reusing the previously built list of albums.  We need to
		 * eliminate password-protected albums and subalbums from it though,
		 * since we can't access them.
		 */
		for ($i = 0; $i < count($albumIDs);) {
			if ($this->readAccessFunctions->album($albumIDs[$i]) !== 1) {
				array_splice($albumIDs, $i, 1);
			} else {
				$i++;
			}
		}
		$query = Photo::with('album')->where(
			function (Builder $query) use ($albumIDs) {
				$query->whereIn('album_id', $albumIDs);
				// Add the 'Unsorted' album.
				if ($this->sessionFunctions->is_logged_in()) {
					$id = $this->sessionFunctions->id();
					$user = User::find($id);
					if ($id == 0 || $user->upload) {
						$query->orWhere('album_id', '=', null);
						if ($id !== 0) {
							$query->where('owner_id', '=', $id);
						}
					}
				}
			});
		for ($i = 0; $i < count($escaped_terms); $i++) {
			$escaped_term = $escaped_terms[$i];
			$query->where(
				function (Builder $query) use ($escaped_term) {
					$query->where('title', 'like', '%' . $escaped_term . '%')
						->orWhere('description', 'like', '%' . $escaped_term . '%')
						->orWhere('tags', 'like', '%' . $escaped_term . '%');
				});
		}
		$photos = $query->get();
		if ($photos != null) {
			$i = 0;
			foreach ($photos as $photo) {
				$return['photos'][$i] = $photo->prepareData();
				$this->symLinkFunctions->getUrl($photo, $return['photos'][$i]);
				$i++;
			}
		}

		// Hash
		$return['hash'] = md5(json_encode($return));

		return $return;
	}
}
