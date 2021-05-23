<?php

namespace App\Actions\Search;

use AccessControl;
use App\Actions\Albums\Extensions\PublicIds;
use App\ModelFunctions\SymLinkFunctions;
use App\Models\Configs;
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
		SymLinkFunctions $symLinkFunctions
	) {
		$this->symLinkFunctions = $symLinkFunctions;
	}

	private function unsorted_or_public(Builder $query)
	{
		if (AccessControl::is_admin()) {
			return $query->orWhere('album_id', '=', null);
		}

		if (AccessControl::can_upload()) {
			$query = $query->orWhere(fn ($q) => $q->where('album_id', '=', null)->where('owner_id', '=', AccessControl::id()));
		}

		if (Configs::get_value('public_photos_hidden', '1') === '0') {
			$query = $query->orWhere('public', '=', 1);
		}

		return $query;
	}

	public function query(array $terms)
	{
		$albumIDs = resolve(PublicIds::class)->getPublicAlbumsId();

		$query = Photo::with('album')
			->where(fn ($q) => $this->unsorted_or_public($q->whereIn('album_id', $albumIDs)));

		foreach ($terms as $escaped_term) {
			$query->where(
				fn (Builder $query) => $query->where('title', 'like', '%' . $escaped_term . '%')
					->orWhere('description', 'like', '%' . $escaped_term . '%')
					->orWhere('tags', 'like', '%' . $escaped_term . '%')
					->orWhere('location', 'like', '%' . $escaped_term . '%')
					->orWhere('model', 'like', '%' . $escaped_term . '%')
					->orWhere('taken_at', 'like', '%' . $escaped_term . '%')
			);
		}

		$photos = $query->get();

		return $photos->map(
			function ($photo) {
				$photo_array = $photo->toReturnArray();
				$this->symLinkFunctions->getUrl($photo, $photo_array);

				return $photo_array;
			}
		);
	}
}
