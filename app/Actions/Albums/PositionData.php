<?php

namespace App\Actions\Albums;

use App\Actions\PhotoAuthorisationProvider;
use App\Exceptions\Internal\QueryBuilderException;
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
	 *
	 * @throws QueryBuilderException
	 */
	public function do(): array
	{
		try {
			$result = [];
			$result['id'] = null;
			$result['title'] = null;
			$result['photos'] = $this->photoAuthorisationProvider->applySearchabilityFilter(
				Photo::with(['album', 'size_variants', 'size_variants.sym_links'])
					->whereNotNull('latitude')
					->whereNotNull('longitude')
			)->get()->toArray();

			return $result;
		} catch (\InvalidArgumentException $e) {
			throw new QueryBuilderException($e);
		}
	}
}
