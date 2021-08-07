<?php

namespace App\Actions\Album;

use App\Contracts\BaseAlbum;
use App\Models\Album;

class PositionData extends Action
{
	public function get(string $albumID, array $data): array
	{
		/** @var BaseAlbum $album */
		$album = $this->albumFactory->findOrFail($albumID);

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
