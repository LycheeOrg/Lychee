<?php

namespace App\Actions\Album;

use App\Models\Album;

class PositionData extends Action
{
	public function get(string $albumID, array $data): array
	{
		// Avoid loading all photos and sub-albums of an album, because we are
		// only interested in a particular subset of photos.
		$album = $this->albumFactory->findOrFail($albumID, false);

		if ($album instanceof Album && $data['includeSubAlbums']) {
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
