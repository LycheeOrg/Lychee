<?php

namespace App\Actions\Albums;

use App\Actions\PhotoAuthorisationProvider;
use App\Models\Configs;
use App\Models\Photo;

class PositionData
{
	protected PhotoAuthorisationProvider $photoAuthorisationProvider;

	public function __construct(PhotoAuthorisationProvider $photoAuthorisationProvider)
	{
		$this->photoAuthorisationProvider = $photoAuthorisationProvider;
		// caching to avoid further request
		Configs::get();
	}

	/**
	 * Given a list of albums, generate an array to be returned.
	 *
	 * @return array
	 */
	public function do(): array
	{
		$result = [];
		$result['id'] = null;
		$result['title'] = null;
		$result['photos'] = $this->photoAuthorisationProvider->applySearchabilityFilter(
			Photo::with(['album', 'size_variants_raw', 'size_variants_raw.sym_links'])
				->whereNotNull('latitude')
				->whereNotNull('longitude')
		)->get()->toArray();

		return $result;
	}
}
