<?php

namespace App\DTO;

use App\Contracts\AbstractAlbum;

/**
 * Data Transfer Object (DTO) for Albums.
 *
 * This allows us to decorate the Album with its associated current user rights.
 */
class AlbumDTO extends DTO
{
	public function __construct(
		private AbstractAlbum $album
	) {
	}

	/**
	 * Album with it's associated righs.
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		$albumDTO = $this->album->toArray();
		$albumDTO['rights'] = AlbumRights::ofAlbum($this->album);

		return $albumDTO;
	}
}