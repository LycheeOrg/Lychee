<?php

namespace App\Actions\Album;

use App\Contracts\AbstractAlbum;
use App\Models\Album;

class PositionData extends Action
{
	public function get(AbstractAlbum $album, bool $includeSubAlbums = false): array
	{
		if ($album instanceof Album && $includeSubAlbums) {
			$photoRelation = $album->all_photos();
		} else {
			$photoRelation = $album->photos();
		}

		$result = [];
		$result['id'] = $album->id;
		$result['title'] = $album->title;
		$result['photos'] = $photoRelation
			->with(['album', 'size_variants', 'size_variants.sym_links'])
			->whereNotNull('latitude')
			->whereNotNull('longitude')
			->get()
			->toArray();

		return $result;
	}
}
