<?php

namespace App\Actions\Albums;

use App\Actions\PhotoAuthorisationProvider;
use App\Contracts\InternalLycheeException;
use App\DTO\PositionData as PositionDataDTO;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
	 * @return PositionDataDTO
	 *
	 * @throws InternalLycheeException
	 */
	public function do(): PositionDataDTO
	{
		$photoQuery = $this->photoAuthorisationProvider->applySearchabilityFilter(
			Photo::query()
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
		);

		return new PositionDataDTO(null, null, $photoQuery->get(), null);
	}
}
