<?php

namespace App\Actions\Album;

use App\Models\Album;

class PositionData extends Action
{
	public function get(string $albumID, array $data): array
	{
		// Avoid to load all photos and sub-albums (if applicable), because
		// we are only interested in a particular subset of photos.
		$album = $this->albumFactory->findOrFail($albumID, false);

		if ($album instanceof Album && $data['includeSubAlbums']) {
			$photoRelation = $album->all_photos();
		} else {
			$photoRelation = $album->photos();
		}

		$return['id'] = $album->id;
		$return['photos'] = $photoRelation
			->with(['album', 'size_variants_raw', 'size_variants_raw.sym_links'])
			->whereNotNull('latitude')
			->whereNotNull('longitude')
			->get()
			->toArray();

		return $return;
	}
}
