<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\ControllerFunctions\ReadAccessFunctions;
use App\ModelFunctions\AlbumActions\Cast as AlbumCast;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\AlbumsFunctions;
use App\ModelFunctions\PhotoActions\Cast as PhotoCast;
use App\ModelFunctions\SessionFunctions;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\User;
use App\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SearchController extends Controller
{
	/**
	 * @var AlbumFunctions
	 */
	private $albumFunctions;

	/**
	 * @var AlbumsFunctions
	 */
	private $albumsFunctions;

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
	 * @param AlbumsFunctions     $albumsFunctions
	 * @param SessionFunctions    $sessionFunctions
	 * @param ReadAccessFunctions $readAccessFunctions
	 * @param SymLinkFunctions    $symLinkFunctions
	 */
	public function __construct(
		AlbumFunctions $albumFunctions,
		AlbumsFunctions $albumsFunctions,
		SessionFunctions $sessionFunctions,
		ReadAccessFunctions $readAccessFunctions,
		SymLinkFunctions $symLinkFunctions
	) {
		$this->albumFunctions = $albumFunctions;
		$this->albumsFunctions = $albumsFunctions;
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
		$toplevel = $this->albumsFunctions->getToplevelAlbums();
		$albumIDs = $this->albumsFunctions->getPublicAlbumsId($toplevel, null, true);

		$query = Album::with([
			'owner',
		])
			->whereIn('id', $albumIDs);
		for ($i = 0; $i < count($escaped_terms); $i++) {
			$escaped_term = $escaped_terms[$i];
			$query->where(
				function (Builder $query) use ($escaped_term) {
					$query->where('title', 'like', '%' . $escaped_term . '%')
						->orWhere('description', 'like', '%' . $escaped_term . '%');
				}
			);
		}
		$albums = $query->get();
		if ($albums != null) {
			$i = 0;
			foreach ($albums as $album_model) {
				$album = AlbumCast::toArray($album_model);

				if ($this->sessionFunctions->is_logged_in()) {
					$album['owner'] = $album_model->owner->username;
				}
				if ($this->readAccessFunctions->album($album_model) === 1) {
					// We don't need 'albums' but we do need to come up with
					// all the subalbums in order to get accurate thumbs info
					// and to let the front end know if there are any.
					$children = $this->albumFunctions->get_children($album_model);

					$album['albums'] = $children->map(function ($e) {
						return AlbumCast::toArray($e[0]);
					});

					$thumbs = $this->albumFunctions->get_thumbs($album_model, $children);
					$this->albumFunctions->set_thumbs($album, $thumbs);
					$album['has_albums'] = count($album['albums']) > 1 ? '1' : '0';
				}

				$return['albums'][$i] = $album;
				$i++;
			}
		}

		/*
		 * Photos.
		 *
		 * We again begin by building a list of all albums and subalbums
		 * accessible from the top level, only this time without
		 * password-protected ones.
		 */
		$albumIDs = $this->albumsFunctions->getPublicAlbumsId($toplevel);
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
			}
		);
		for ($i = 0; $i < count($escaped_terms); $i++) {
			$escaped_term = $escaped_terms[$i];
			$query->where(
				function (Builder $query) use ($escaped_term) {
					$query->where('title', 'like', '%' . $escaped_term . '%')
						->orWhere('description', 'like', '%' . $escaped_term . '%')
						->orWhere('tags', 'like', '%' . $escaped_term . '%')
						->orWhere('location', 'like', '%' . $escaped_term . '%');
				}
			);
		}
		/**
		 * @var Photo[]
		 */
		$photos = $query->get();
		if ($photos != null) {
			$i = 0;
			foreach ($photos as $photo) {
				$return['photos'][$i] = PhotoCast::toArray($photo);
				PhotoCast::urls($return['photos'][$i], $photo);
				$this->symLinkFunctions->getUrl($photo, $return['photos'][$i]);
				$i++;
			}
		}

		// Hash
		$return['hash'] = md5(json_encode($return));

		return $return;
	}
}
