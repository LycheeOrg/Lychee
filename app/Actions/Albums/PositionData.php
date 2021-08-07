<?php

namespace App\Actions\Albums;

use App\Actions\Albums\Extensions\PublicIds;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Collection;

class PositionData
{
	/**
	 * Given a list of albums, generate an array to be returned.
	 *
	 * @return Collection
	 */
	public function do(): Collection
	{
		// caching to avoid further request
		Configs::get();
		$publicAlbumsId = resolve(PublicIds::class)->getPublicAlbumsId();

		return Photo::with(['album', 'size_variants_raw', 'size_variants_raw.sym_links'])
			->whereNotNull('latitude')
			->whereNotNull('longitude')
			->whereIn('album_id', $publicAlbumsId)
			->get();
	}
}
