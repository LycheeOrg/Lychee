<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use AccessControl;
use App\Actions\Albums\Extensions\PublicIds;
use App\Actions\Albums\Top;
use App\Actions\ReadAccessFunctions;
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
	use PublicIds;

	/**
	 * @var readAccessFunctions
	 */
	private $readAccessFunctions;

	/**
	 * @var SymLinkFunctions
	 */
	private $symLinkFunctions;

	/**
	 * @var Top
	 */
	private $top;

	/**
	 * @param SessionFunctions    $sessionFunctions
	 * @param ReadAccessFunctions $readAccessFunctions
	 * @param SymLinkFunctions    $symLinkFunctions
	 */
	public function __construct(
		ReadAccessFunctions $readAccessFunctions,
		SymLinkFunctions $symLinkFunctions,
		Top $top
	) {
		$this->readAccessFunctions = $readAccessFunctions;
		$this->symLinkFunctions = $symLinkFunctions;
		$this->top = $top;
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
		if (!AccessControl::is_logged_in() && Configs::get_value('public_search', '0') !== '1') {
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
		$albumIDs = $this->getPublicAlbumsId();

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
				$album = $album_model->toReturnArray();

				if (AccessControl::is_logged_in()) {
					$album['owner'] = $album_model->owner->username;
				}
				if ($this->readAccessFunctions->album($album_model) === 1) {
					// We don't need 'albums' but we do need to come up with
					// all the subalbums in order to get accurate thumbs info
					// and to let the front end know if there are any.
					$thumbs = $this->album_model->get_thumbs();
					$this->album_model->set_thumbs($album, $thumbs);
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
		$albumIDs = $this->getPublicAlbumsId();
		$query = Photo::with('album')->where(
			function (Builder $query) use ($albumIDs) {
				$query->whereIn('album_id', $albumIDs);
				// Add the 'Unsorted' album.
				if (AccessControl::is_logged_in()) {
					$id = AccessControl::id();
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
				$return['photos'][$i] = $photo->toReturnArray();
				$photo->urls($return['photos'][$i]);
				$this->symLinkFunctions->getUrl($photo, $return['photos'][$i]);
				$i++;
			}
		}

		// Hash
		$return['hash'] = md5(json_encode($return));

		return $return;
	}
}
