<?php

namespace App\Actions\Albums;

use App\Actions\AlbumAuthorisationProvider;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;

class PositionData
{
	protected AlbumAuthorisationProvider $albumAuthorisationProvider;

	public function __construct()
	{
		$this->albumAuthorisationProvider = resolve(AlbumAuthorisationProvider::class);
		// caching to avoid further request
		Configs::get();
	}

	/**
	 * Given a list of albums, generate an array to be returned.
	 *
	 * @return array
	 *
	 * @throws QueryBuilderException
	 */
	public function do(): array
	{
		$result = [];
		$result['id'] = null;
		$result['title'] = null;
		try {
			$result['photos'] = Photo::with(['album', 'size_variants_raw', 'size_variants_raw.sym_links'])
				->whereHas('album', fn (Builder $q) => $this->albumAuthorisationProvider->applyAccessibilityFilter($q))
				->whereNotNull('latitude')
				->whereNotNull('longitude')
				->get()
				->toArray();
		} catch (\RuntimeException $e) {
			throw new QueryBuilderException($e);
		}

		return $result;
	}
}
