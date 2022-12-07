<?php

namespace App\DTO;

use App\Contracts\AbstractAlbum;
use App\DTO\Rights\AlbumRightsDTO;
use App\Models\Album;

/**
 * Data Transfer Object (DTO) for Abstract Albums.
 *
 * This allows us to decorate the album with its associated current user rights.
 */
class AbstractAlbumDTO extends AbstractDTO
{
	public function __construct(
		private AbstractAlbum $album
	) {
	}

	/**
	 * Abstract Album with it's associated rights.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		// Base conversion to array
		$albumDTO = $this->album->toArray();

		// If album provides sub-albums also apply conversion to them
		if (key_exists('albums', $albumDTO) && $this->album instanceof Album) {
			/** @var \Illuminate\Support\Collection<int,Album> $children */
			$children = $this->album->children;
			$albumDTO['albums'] = $children->map(fn (Album $a) => ((new AbstractAlbumDTO($a))->toArray()));
		}

		// add the rights
		$albumDTO['rights'] = AlbumRightsDTO::ofAlbum($this->album);

		return $albumDTO;
	}
}