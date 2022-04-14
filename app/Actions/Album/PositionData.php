<?php

namespace App\Actions\Album;

use App\Contracts\AbstractAlbum;
use App\Models\Album;
use App\Models\SizeVariant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
			->with([
				'album' => function (BelongsTo $b) {
					// The album is required for photos to properly
					// determine access and visibility rights; but we
					// don't need to determine the cover and thumbnail for
					// each album
					$b->without(['cover', 'thumb']);
				},
				'size_variants' => function (HasMany $r) {
					// The web GUI only uses the small and thumb size
					// variants to show photos on a map; so we can save
					// hydrating the larger size variants
					// this really helps, if you want to show thousands
					// of photos on a map, as there are up to 7 size
					// variants per photo
					$r->whereBetween('type', [SizeVariant::SMALL2X, SizeVariant::THUMB]);
				},
				'size_variants.sym_links',
			])
			->whereNotNull('latitude')
			->whereNotNull('longitude')
			->get()
			->toArray();

		return $result;
	}
}
