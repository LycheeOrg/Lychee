<?php

namespace App\Actions\Search;

use AccessControl;
use App\Actions\Album\Photos;
use App\Actions\Albums\Extensions\PublicIds;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;

class PhotoSearch
{
	/**
	 * @var SymLinkFunctions
	 */
	private $symLinkFunctions;

	/**
	 * @param SymLinkFunctions $symLinkFunctions
	 */
	public function __construct(
		SymLinkFunctions $symLinkFunctions,
		Photos $photosAction
	) {
		$this->symLinkFunctions = $symLinkFunctions;
		$this->photosAction = $photosAction;
	}

	private function unsorted(Builder $query)
	{
		if (!AccessControl::is_logged_in()) {
			return $query;
		}

		if (AccessControl::is_admin()) {
			return $query->orWhere('album_id', '=', null);
		}

		if (AccessControl::can_upload()) {
			return $query->orWhere(fn ($q) => $q->where('album_id', '=', null)->where('owner_id', '=', AccessControl::id()));
		}
	}

	public function query(array $terms)
	{
		$albumIDs = resolve(PublicIds::class)->getPublicAlbumsId();

		$query = Photo::with('album')
			->where(fn ($q) => $this->unsorted($q->whereIn('album_id', $albumIDs)));

		foreach ($terms as $escaped_term) {
			$query->where(
				fn (Builder $query) => $query->where('title', 'like', '%' . $escaped_term . '%')
					->orWhere('description', 'like', '%' . $escaped_term . '%')
					->orWhere('tags', 'like', '%' . $escaped_term . '%')
					->orWhere('location', 'like', '%' . $escaped_term . '%')
			);
		}

		$photos = $query->get();

		return $this->photosAction->getPhotos($photos);
	}
}
