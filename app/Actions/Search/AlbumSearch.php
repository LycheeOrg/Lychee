<?php

namespace App\Actions\Search;

use App\Actions\Albums\Extensions\PublicIds;
use App\Facades\AccessControl;
use App\Models\Album;
use Illuminate\Database\Eloquent\Builder;

class AlbumSearch
{
	public function query(array $terms)
	{
		$albumIDs = resolve(PublicIds::class)->getPublicAlbumsId();

		$query = Album::with(['owner'])->whereIn('id', $albumIDs);

		foreach ($terms as $term) {
			$query->where(
				fn (Builder $query) => $query->where('title', 'like', '%' . $term . '%')
					->orWhere('description', 'like', '%' . $term . '%')
			);
		}

		$albums = $query->get();

		return $albums->map(function ($album_model) {
			$album = $album_model->toReturnArray();

			if (AccessControl::is_logged_in()) {
				$album['owner'] = $album_model->owner->username;
			}

			return $album;
		});
	}
}
